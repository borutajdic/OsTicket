<?php
/*********************************************************************
    ajax.reports.php

    AJAX interface for reports -- both plot and tabular data are retrievable
    in JSON format from this utility. Please put plumbing in /scp/ajax.php
    pattern rules.

    Jared Hancock <jared@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

if(!defined('INCLUDE_DIR')) die('403');

include_once(INCLUDE_DIR.'class.ticket.php');

/**
 * Overview Report
 *
 * The overview report allows for the display of basic ticket statistics in
 * both graphical and tabular formats.
 */
class OverviewReportAjaxAPI extends AjaxController {
    function enumTabularGroups() {
        return $this->encode(array("dept"=>__("Department"), "topic"=>__("Topics"), "customer" => __("Stranka"),
            # XXX: This will be relative to permissions based on the
            # logged-in-staff. For basic staff, this will be 'My Stats'
            "staff"=>__("Agent"), "time"=>__("Čas dela"), "org"=>__("Organizacija")));
    }

    function getData() {
        global $thisstaff;

        list($start, $stop) = $this->_getDateRange();

        $groups = array(
            "dept" => array(
                "table" => DEPT_TABLE,
                "pk" => "dept_id",
                "sort" => 'T1.dept_name',
                "fields" => 'T1.dept_name',
                "headers" => array(__('Department')),
                "filter" => ('T1.dept_id IN ('.implode(',', db_input($thisstaff->getDepts())).')')
            ),
            "topic" => array(
                "table" => TOPIC_TABLE,
                "pk" => "topic_id",
                "sort" => 'name',
                "fields" => "CONCAT_WS(' / ',"
                    ."(SELECT P.topic FROM ".TOPIC_TABLE." P WHERE P.topic_id = T1.topic_pid),"
                    ."T1.topic) as name ",
                "headers" => array(__('Help Topic')),
                "filter" => '1'
            ),
            "staff" => array(
                "table" => STAFF_TABLE,
                "pk" => 'staff_id',
                "sort" => 'name',
                "fields" => "CONCAT_WS(' ', T1.firstname, T1.lastname) as name",
                "headers" => array(__('Agent')),
                "filter" =>
                    ('T1.staff_id=S1.staff_id
                      AND
                      (T1.staff_id='.db_input($thisstaff->getId())
                        .(($depts=$thisstaff->getManagedDepartments())?
                            (' OR T1.dept_id IN('.implode(',', db_input($depts)).')'):'')
                        .(($thisstaff->canViewStaffStats())?
                            (' OR T1.dept_id IN('.implode(',', db_input($thisstaff->getDepts())).')'):'')
                     .')'
                     )
				#od odklepaja dalje moje
			),
			
			 "customer" => array(
                "table" => USER_TABLE,
                "pk" => 'id',
                "sort" => 'name',
                "fields" => 'name',
                "headers" => array(__('Stranka')),
				"filter" => '1'
            ),
			"time" => array(
				"table" => 'ost_ticket_time',
				"headers" => array(__('Stranka'))
			),
			"org" => array(
				"table" => 'ost_organization',
				"headers" => array(__('Organizacija'))
			)
        );
        $group = $this->get('group', 'dept');
        $info = isset($groups[$group])?$groups[$group]:$groups['dept'];

        # XXX: Die if $group not in $groups
		//izpise informacije o skupnem casu za stranko po vrsti dela
		if((strcmp($info['table'], "ost_ticket_time")) === 0){
			$queries=array(array(1,' SELECT u.name, sum(time.time_spent) as Brezplačno
				FROM ost_user u LEFT JOIN ost_ticket t ON (u.id = t.user_id) LEFT JOIN ost_ticket_time time ON (time.ticket_id = t.ticket_id)
				WHERE time.vrsta_dela_id = 1 AND t.closed BETWEEN '.$start.' AND '.$stop.'
				GROUP BY u.name'),
						array(1,' SELECT u.name, sum(time.time_spent) as Delo
				FROM ost_user u LEFT JOIN ost_ticket t ON (u.id = t.user_id) LEFT JOIN ost_ticket_time time ON (time.ticket_id = t.ticket_id)
				WHERE time.vrsta_dela_id = 17 AND t.closed BETWEEN '.$start.' AND '.$stop.'
				GROUP BY u.name'),
						array(1,' SELECT u.name, sum(time.time_spent) as Programer
				FROM ost_user u LEFT JOIN ost_ticket t ON (u.id = t.user_id) LEFT JOIN ost_ticket_time time ON (time.ticket_id = t.ticket_id)
				WHERE time.vrsta_dela_id = 18 AND t.closed BETWEEN '.$start.' AND '.$stop.'
				GROUP BY u.name'),
						array(1,' SELECT u.name, sum(time.time_spent) as Inženir
				FROM ost_user u LEFT JOIN ost_ticket t ON (u.id = t.user_id) LEFT JOIN ost_ticket_time time ON (time.ticket_id = t.ticket_id)
				WHERE time.vrsta_dela_id = 19 AND t.closed BETWEEN '.$start.' AND '.$stop.'
				GROUP BY u.name'),
						array(1,' SELECT u.name, sum(time.time_spent) as Interno
				FROM ost_user u LEFT JOIN ost_ticket t ON (u.id = t.user_id) LEFT JOIN ost_ticket_time time ON (time.ticket_id = t.ticket_id)
				WHERE time.vrsta_dela_id = 20 AND t.closed BETWEEN '.$start.' AND '.$stop.'
				GROUP BY u.name'),
						array(1,' SELECT u.name, sum(time.time_spent) as Potovalna_ura
				FROM ost_user u LEFT JOIN ost_ticket t ON (u.id = t.user_id) LEFT JOIN ost_ticket_time time ON (time.ticket_id = t.ticket_id)
				WHERE time.vrsta_dela_id = 27 AND t.closed BETWEEN '.$start.' AND '.$stop.'
				GROUP BY u.name'),
						array(1,' SELECT u.name, sum(time.time_spent) as Kilometrina
				FROM ost_user u LEFT JOIN ost_ticket t ON (u.id = t.user_id) LEFT JOIN ost_ticket_time time ON (time.ticket_id = t.ticket_id)
				WHERE time.vrsta_dela_id = 28 AND t.closed BETWEEN '.$start.' AND '.$stop.'
				GROUP BY u.name')
			);
			$rows = array();
			$cols = 1;
			foreach ($queries as $q) {
				list($c, $sql) = $q;
				$res = db_query($sql);
				$cols += $c;
				while ($row = db_fetch_row($res)) {
					$found = false;
					foreach ($rows as &$r) {
						if ($r[0] == $row[0]) {
							$r = array_merge($r, array_slice($row, -$c));
							$found = true;
							break;
						}
					}
					if (!$found)
						$rows[] = array_merge(array($row[0]), array_slice($row, -$c));
				}
				# Make sure each row has the same number of items
				foreach ($rows as &$r)
					while (count($r) < $cols)
						$r[] = null;
			}
			$sql1 = 'SELECT opis FROM ost_vrsta_dela';
			$exe = db_query($sql1);
			while($data = db_fetch_array($exe)){
				$datarow[] = $data['opis'];
			}
			
			return array("columns" => array_merge($info['headers'],$datarow),"data" => $rows);
		}
		//izpise case po organizacijah glede na vrsto dela
		else if((strcmp($info['table'], "ost_organization")) === 0){
			$queries=array(array(1,' SELECT o.name, sum(time.time_spent) as Brezplačno FROM ost_user u 
					LEFT JOIN ost_organization o ON (o.id = u.org_id) LEFT JOIN ost_ticket t ON (u.id = t.user_id) LEFT JOIN ost_ticket_time time ON (time.ticket_id = t.ticket_id)
			WHERE time.vrsta_dela_id = 1 AND t.closed BETWEEN '.$start.' AND '.$stop.' AND o.name is not null
			GROUP BY o.name'),
					array(1,' SELECT o.name, sum(time.time_spent) as Delo FROM ost_user u 
					LEFT JOIN ost_organization o ON (o.id = u.org_id) LEFT JOIN ost_ticket t ON (u.id = t.user_id) LEFT JOIN ost_ticket_time time ON (time.ticket_id = t.ticket_id)
			WHERE time.vrsta_dela_id = 17 AND t.closed BETWEEN '.$start.' AND '.$stop.'	AND o.name is not null
			GROUP BY o.name'),
					array(1,' SELECT o.name, sum(time.time_spent) as Programer FROM ost_user u 
					LEFT JOIN ost_organization o ON (o.id = u.org_id) LEFT JOIN ost_ticket t ON (u.id = t.user_id) LEFT JOIN ost_ticket_time time ON (time.ticket_id = t.ticket_id)
			WHERE time.vrsta_dela_id = 18 AND t.closed BETWEEN '.$start.' AND '.$stop.'	AND o.name is not null
			GROUP BY o.name'),
					array(1,' SELECT o.name, sum(time.time_spent) as Inženir FROM ost_user u 
					LEFT JOIN ost_organization o ON (o.id = u.org_id) LEFT JOIN ost_ticket t ON (u.id = t.user_id) LEFT JOIN ost_ticket_time time ON (time.ticket_id = t.ticket_id)
			WHERE time.vrsta_dela_id = 19 AND t.closed BETWEEN '.$start.' AND '.$stop.' AND o.name is not null
			GROUP BY o.name'),
					array(1,' SELECT o.name, sum(time.time_spent) as Interno FROM ost_user u 
					LEFT JOIN ost_organization o ON (o.id = u.org_id) LEFT JOIN ost_ticket t ON (u.id = t.user_id) LEFT JOIN ost_ticket_time time ON (time.ticket_id = t.ticket_id)
			WHERE time.vrsta_dela_id = 20 AND t.closed BETWEEN '.$start.' AND '.$stop.' AND o.name is not null
			GROUP BY o.name'),
					array(1,'  SELECT o.name, sum(time.time_spent) as Potovalna_ura FROM ost_user u 
					LEFT JOIN ost_organization o ON (o.id = u.org_id) LEFT JOIN ost_ticket t ON (u.id = t.user_id) LEFT JOIN ost_ticket_time time ON (time.ticket_id = t.ticket_id)
			WHERE time.vrsta_dela_id = 27 AND t.closed BETWEEN '.$start.' AND '.$stop.' AND o.name is not null
			GROUP BY o.name'),
					array(1,' SELECT o.name, sum(time.time_spent) as Kilometrina FROM ost_user u 
					LEFT JOIN ost_organization o ON (o.id = u.org_id) LEFT JOIN ost_ticket t ON (u.id = t.user_id) LEFT JOIN ost_ticket_time time ON (time.ticket_id = t.ticket_id)
			WHERE time.vrsta_dela_id = 28 AND t.closed BETWEEN '.$start.' AND '.$stop.' AND o.name is not null
			GROUP BY o.name')
			);
			$rows = array();
			$cols = 1;
			foreach ($queries as $q) {
				list($c, $sql) = $q;
				$res = db_query($sql);
				$cols += $c;
				while ($row = db_fetch_row($res)) {
					$found = false;
					foreach ($rows as &$r) {
						if ($r[0] == $row[0]) {
							$r = array_merge($r, array_slice($row, -$c));
							$found = true;
							break;
						}
					}
					if (!$found)
						$rows[] = array_merge(array($row[0]), array_slice($row, -$c));
				}
				# Make sure each row has the same number of items
				foreach ($rows as &$r)
					while (count($r) < $cols)
						$r[] = null;
			}
			$sql1 = 'SELECT opis FROM ost_vrsta_dela';
			$exe = db_query($sql1);
			while($data = db_fetch_array($exe)){
				$datarow[] = $data['opis'];
			}	
			return array("columns" => array_merge($info['headers'],$datarow),"data" => $rows);
		}
		//izpise stevilo resenih zahtev in skupno stevilo ur po stranki
		else  if ((strcmp($info['table'], "ost_user")) === 0){
			$queries=array(
				array(5, 'SELECT T1.'.$info['fields'].',
					COUNT(A1.ticket_id)-COUNT(NULLIF(A1.state, "created")) AS Opened,
					COUNT(A1.ticket_id)-COUNT(NULLIF(A1.state, "assigned")) AS Assigned,
					COUNT(A1.ticket_id)-COUNT(NULLIF(A1.state, "overdue")) AS Overdue,
					COUNT(A1.ticket_id)-COUNT(NULLIF(A1.state, "closed")) AS Closed,
					COUNT(A1.ticket_id)-COUNT(NULLIF(A1.state, "reopened")) AS Reopened
				FROM '.$info['table'].' T1
					LEFT JOIN '.TICKET_TABLE.' TI ON (TI.user_id = T1.id)
					LEFT JOIN '.TICKET_EVENT_TABLE.' A1
						ON (A1.ticket_id = TI.ticket_id
							 AND NOT annulled
							 AND (A1.timestamp BETWEEN '.$start.' AND '.$stop.'))
					LEFT JOIN '.STAFF_TABLE.' S1 ON (S1.staff_id=A1.staff_id)
				GROUP BY T1.'.$info['pk'].'
				ORDER BY T1.'.$info['sort']),
				
				array(2, 'select u.name, format(sum(time.time_spent),2) as time, FORMAT(sum(time.time),1) AS Dejanski
							from ost_user u 
							left join ost_ticket t on (u.id = t.user_id)
							left join ost_ticket_time time on (time.ticket_id = t.ticket_id)
							where t.closed between '.$start.' and '.$stop.'
							group by u.name
							order by u.name'),
			);
		}
		//izpise stevilo zahtevkov po oddelku temi in zaposlenemu
		else{
			$queries=array(
				array(5, 'SELECT '.$info['fields'].',
					COUNT(A1.ticket_id)-COUNT(NULLIF(A1.state, "created")) AS Opened,
					COUNT(A1.ticket_id)-COUNT(NULLIF(A1.state, "assigned")) AS Assigned,
					COUNT(A1.ticket_id)-COUNT(NULLIF(A1.state, "overdue")) AS Overdue,
					COUNT(A1.ticket_id)-COUNT(NULLIF(A1.state, "closed")) AS Closed,
					COUNT(A1.ticket_id)-COUNT(NULLIF(A1.state, "reopened")) AS Reopened
				FROM '.$info['table'].' T1
					LEFT JOIN '.TICKET_EVENT_TABLE.' A1
						ON (A1.'.$info['pk'].'=T1.'.$info['pk'].'
							 AND NOT annulled
							 AND (A1.timestamp BETWEEN '.$start.' AND '.$stop.'))
					LEFT JOIN '.STAFF_TABLE.' S1 ON (S1.staff_id=A1.staff_id)
					
				WHERE '.$info['filter'].'
				GROUP BY T1.'.$info['pk'].'
				ORDER BY '.$info['sort']),
				
				array(2, 'SELECT '.$info['fields'].',
					FORMAT(sum(time.time_spent),1) AS ServiceTime,
					FORMAT(sum(time.time),1) AS Dejanski
				FROM '.$info['table'].' T1
					LEFT JOIN '.TICKET_TABLE.' T2 ON (T2.'.$info['pk'].'=T1.'.$info['pk'].')
					LEFT JOIN '.STAFF_TABLE.' S1 ON (S1.staff_id=T2.staff_id)
					LEFT JOIN ost_ticket_time time ON (T2.ticket_id = time.ticket_id)
				WHERE '.$info['filter'].' AND T2.closed BETWEEN '.$start.' AND '.$stop.'
				GROUP BY T1.'.$info['pk'].'
				ORDER BY '.$info['sort'])
			);
		}
        $rows = array();
        $cols = 1;
        foreach ($queries as $q) {
            list($c, $sql) = $q;
            $res = db_query($sql);
            $cols += $c;
            while ($row = db_fetch_row($res)) {
                $found = false;
                foreach ($rows as &$r) {
                    if ($r[0] == $row[0]) {
                        $r = array_merge($r, array_slice($row, -$c));
                        $found = true;
                        break;
                    }
                }
                if (!$found)
                    $rows[] = array_merge(array($row[0]), array_slice($row, -$c));
            }
            # Make sure each row has the same number of items
            foreach ($rows as &$r)
                while (count($r) < $cols)
                    $r[] = null;
        }
        return array("columns" => array_merge($info['headers'],
                        array(__('Opened'),__('Assigned'),__('Overdue'),__('Closed'),__('Reopened'),
                              __('Service Time'),__('Dejanski čas'))),
                     "data" => $rows);
    }

    function getTabularData() {
        return $this->encode($this->getData());
    }

    function downloadTabularData() {
        $data = $this->getData();
        $csv = '"' . implode('","',$data['columns']) . '"';
        foreach ($data['data'] as $row)
            $csv .= "\n" . '"' . implode('","', $row) . '"';
        Http::download(
            sprintf('%s-report.csv', $this->get('group', __('Department'))),
            'text/csv', $csv);
    }

    function _getDateRange() {
        global $cfg;

        if(($start = $this->get('start', 'last month'))) {
            $stop = $this->get('period', 'now');
        } else {
            $start = 'last month';
            $stop = $this->get('period', 'now');
        }

        if ($start != 'last month')
            $start = DateTime::createFromFormat($cfg->getDateFormat(),
                $start)->format('U');
        else
            $start = strtotime($start);

        if (substr($stop, 0, 1) == '+')
            $stop = strftime('%Y-%m-%d ', $start) . $stop;

        $start = 'FROM_UNIXTIME('.$start.')';
        $stop = 'FROM_UNIXTIME('.strtotime($stop).')';

        return array($start, $stop);
    }

    function getPlotData() {
        list($start, $stop) = $this->_getDateRange();

        # Fetch all types of events over the timeframe
        $res = db_query('SELECT DISTINCT(state) FROM '.TICKET_EVENT_TABLE
            .' WHERE timestamp BETWEEN '.$start.' AND '.$stop
                .' ORDER BY 1');
        $events = array();
        while ($row = db_fetch_row($res)) $events[] = $row[0];

        # TODO: Handle user => db timezone offset
        # XXX: Implement annulled column from the %ticket_event table
        $res = db_query('SELECT state, DATE_FORMAT(timestamp, \'%Y-%m-%d\'), '
                .'COUNT(ticket_id)'
            .' FROM '.TICKET_EVENT_TABLE
            .' WHERE timestamp BETWEEN '.$start.' AND '.$stop
            .' AND NOT annulled'
            .' GROUP BY state, DATE_FORMAT(timestamp, \'%Y-%m-%d\')'
            .' ORDER BY 2, 1');
        # Initialize array of plot values
        $plots = array();
        foreach ($events as $e) { $plots[$e] = array(); }

        $time = null; $times = array();
        # Iterate over result set, adding zeros for missing ticket events
        $slots = array();
        while ($row = db_fetch_row($res)) {
            $row_time = strtotime($row[1]);
            if ($time != $row_time) {
                # New time (and not the first), figure out which events did
                # not have any tickets associated for this time slot
                if ($time !== null) {
                    # Not the first record -- add zeros all the arrays that
                    # did not have at least one entry for the timeframe
                    foreach (array_diff($events, $slots) as $slot)
                        $plots[$slot][] = 0;
                }
                $slots = array();
                $times[] = $time = $row_time;
            }
            # Keep track of states for this timeframe
            $slots[] = $row[0];
            $plots[$row[0]][] = (int)$row[2];
        }
        foreach (array_diff($events, $slots) as $slot)
            $plots[$slot][] = 0;
        return $this->encode(array("times" => $times, "plots" => $plots,
            "events"=>$events));
    }
}