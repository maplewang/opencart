<?php
class ControllerStartupSession extends Controller {
	public function index() {
		if (isset($this->request->get['token']) && isset($this->request->get['route']) && substr($this->request->get['route'], 0, 4) == 'api/') {
			//var_dump("session.php Delete Seesion");
			if ($this->config->get('db_type') == "CSQLite3") {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "api_session` WHERE datetime(date_modified, '+60 Minute') < datetime('now')");
			} else {
				$this->db->query("DELETE FROM `" . DB_PREFIX . "api_session` WHERE TIMESTAMPADD(HOUR, 1, date_modified) < datetime('now')");
			}
			//$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "api` `a` LEFT JOIN `" . DB_PREFIX . "api_session` `as` ON (a.api_id = as.api_id) LEFT JOIN " . DB_PREFIX . "api_ip `ai` ON (as.api_id = ai.api_id) WHERE a.status = '1' AND as.token = '" . $this->db->escape($this->request->get['token']) . "' AND ai.ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "'");
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "api` `a` LEFT JOIN `" . DB_PREFIX . "api_session` as apis ON (a.api_id = apis.api_id) LEFT JOIN " . DB_PREFIX . "api_ip `ai` ON (apis.api_id = ai.api_id) WHERE a.status = '1' AND apis.token = '" . $this->db->escape($this->request->get['token']) . "' AND ai.ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "'");
			if ($query->num_rows) {
				$this->session->start('api', $query->row['session_id']);
				
				// keep the session alive
				$this->db->query("UPDATE `" . DB_PREFIX . "api_session` SET date_modified = datetime('now') WHERE api_session_id = '" . (int)$query->row['api_session_id'] . "'");
			}
		} else {
			$this->session->start();
		}
	}
}