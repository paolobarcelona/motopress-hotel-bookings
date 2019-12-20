<?php

namespace MPHB\iCal;

class Logger
{
    const TABLE_NAME = 'mphb_sync_logs';

	/** @var int */
	protected $queueId = 0;

    /** @var string Table name. */
    protected $mphb_sync_logs = '';

	public function __construct( $queueId = 0 ){
        global $wpdb;

        $this->setQueueId($queueId);
        $this->mphb_sync_logs = $wpdb->prefix . self::TABLE_NAME;
	}

	public function setQueueId( $queueId ){
		$this->queueId = intval($queueId);
	}

	public function getQueueId()
	{
		return $this->queueId;
	}

	public function success( $message, $context = array() ){
		$this->log( 'success', $message, $context );
	}

	public function info( $message, $context = array() ){
		$this->log( 'info', $message, $context );
	}

	public function warning( $message, $context = array() ){
		$this->log( 'warning', $message, $context );
	}

	public function error( $message, $context = array() ){
		$this->log( 'error', $message, $context );
	}

	/**
	 * @param string $status "success"|"info"|"warning"|"error"
	 * @param string $message
	 * @param array $context Room ID, PRODID, UID, check-in/check-out dates etc.
	 */
	public function log( $status, $message, $context = array() ){
		global $wpdb;

        if (empty($this->queueId)) {
            return;
        }

		// All the data must be scalar, or it will generate a warning similar
		// like "expects parameter 1 to be string, array given". See
		// https://codex.wordpress.org/Class_Reference/wpdb#INSERT_row
		$context = maybe_serialize( $context );

		$wpdb->insert($this->mphb_sync_logs, array(
			'queue_id' => $this->queueId,
			'log_status'   => $status,
			'log_message'  => $message,
			'log_context'  => $context
		));
	}

    public function getLogs($skipCount = 0)
    {
        return Logger::selectLogs($this->queueId, $skipCount, 400000000);
    }

    public function clear()
    {
        if (!empty($this->queueId)) {
            Logger::deleteQueue($this->queueId);
        }
    }

    public static function selectLogs($queueId, $offset, $limit)
    {
        global $wpdb;

        $mphb_sync_logs = $wpdb->prefix . Logger::TABLE_NAME;

        $query = $wpdb->prepare(
            "SELECT log_status, log_message, log_context"
                . " FROM {$mphb_sync_logs}"
                . " WHERE queue_id = %d"
                . " LIMIT {$offset}, {$limit}",
            $queueId
        );

        $rows = $wpdb->get_results($query, ARRAY_A);

        $logs = array_map(function ($row) {
            return array(
                'status'  => $row['log_status'],
                'message' => $row['log_message'],
                'context' => maybe_unserialize($row['log_context'])
            );
        }, $rows);

        return $logs;
    }

    public static function countLogs($queueId)
    {
        global $wpdb;

        $mphb_sync_logs = $wpdb->prefix . Logger::TABLE_NAME;

        $query = $wpdb->prepare(
            "SELECT COUNT(*)"
                . " FROM {$mphb_sync_logs}"
                . " WHERE queue_id = %d",
            $queueId
        );

        return $wpdb->get_var($query);
    }

	/**
	 * @param int $queueId
	 *
	 * @global \wpdb $wpdb
	 */
	public static function deleteQueue($queueId)
	{
		global $wpdb;

        $mphb_sync_logs = $wpdb->prefix . Logger::TABLE_NAME;

		$query = $wpdb->prepare(
            "DELETE FROM {$mphb_sync_logs}"
                . " WHERE queue_id = %d",
            $queueId
        );

		$wpdb->query($query);
	}

    /**
     * @param int[] $queueIds
     *
     * @global \wpdb $wpdb
     */
    public static function deleteQueues($queueIds)
    {
        global $wpdb;

        $mphb_sync_logs = $wpdb->prefix . Logger::TABLE_NAME;
        $query = "DELETE FROM {$mphb_sync_logs} WHERE queue_id IN (" . implode(', ', $queueIds) . ")";

        $wpdb->query($query);
    }

    /**
     * Delete all logs, where queue status is "wait", "in-progress" or "done",
     * but leave logs of the "auto"-items.
     *
     * @global \wpdb $wpdb
     */
	public static function deleteSync()
	{
		global $wpdb;

        $mphb_sync_logs  = $wpdb->prefix . Logger::TABLE_NAME;
        $mphb_sync_queue = $wpdb->prefix . Queue::TABLE_NAME;

        $query = $wpdb->prepare(
            "DELETE logs FROM {$mphb_sync_logs} AS logs"
                . " INNER JOIN {$mphb_sync_queue} AS queue ON logs.queue_id = queue.queue_id"
                . " WHERE queue.queue_status != %s",
            Queue::STATUS_AUTO
        );

		$wpdb->query($query);
	}

    /**
     * Sometime background process may add a log message before it recognizes,
     * that it was aborted. Delete those log message.
     *
     * @global \wpdb $wpdb
     */
    public static function deleteGhosts()
    {
        global $wpdb;

        $mphb_sync_logs  = $wpdb->prefix . Logger::TABLE_NAME;
        $mphb_sync_queue = $wpdb->prefix . Queue::TABLE_NAME;

        $query = "DELETE logs FROM {$mphb_sync_logs} AS logs"
            . " LEFT JOIN {$mphb_sync_queue} AS queue ON logs.queue_id = queue.queue_id"
            . " WHERE queue.queue_id IS NULL";

        return $wpdb->query($query);
    }
}
