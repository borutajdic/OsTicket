#!/bin/sh

echo Zaèetek skripte

#stranka potrdi zahtevo
\cp ticket-potrjeni.php /var/www/html/helpdesk/include/staff/ticket-potrjeni.php
#P.S:Hardoced je noter id od statusa Potrjeno s strani stranke, ki je trenutno 7 v vrstici 185
#
\cp ticket-aktivnost.php /var/www/html/helpdesk/include/staff/ticket-aktivnost.php



#koledar zahtevkov
\cp calendar.php /var/www/html/helpdesk/scp/calendar.php
#popup na koledarju
\cp calendar.modal.php /var/www/html/helpdesk/scp/calendar.modal.php
#koledar - styles
\cp calendar.css /var/www/html/helpdesk/scp/calendar.css

#izpiše zahteve v doloèenem datumu + tiskanje
\cp org.zahteve.php /var/www/html/helpdesk/scp/org.zahteve.php
#poišèe zahteve
\cp org.get.php /var/www/html/helpdesk/scp/org.get.php

#ALTER TABLE ost_staff ADD isAtWork int(1) not null default '0';
#CREATE TABLE ost_vrsta_dela(id int(11) NOT NULL, koda varchar(5) NULL, opis varchar(50) NOT NULL, PRIMARY KEY(id));
#CREATE TABLE ost_ticket_time(ticket_id int(11) NOT NULL, staff_id int(11) NOT NULL, time_spent float(4,2) NOT NULL, vrsta_dela_id int(11) NOT NULL, opis char(64) NULL, id int(11) not null auto_incrament, time float(4,2) not null default '0.00', primary key(id));
#CREATE TABLE ost_aktivnost(id int(11) NOT NULL, name char(64) NOT NULL, PRIMARY KEY(id));
#CREATE TABLE ost_agent_aktivnost(id int(11) NOT NULL AUTO_INCREMENT,staff_id int(11) NOT NULL,aktivnost_id int(11) NOT NULL,opis char(64) NULL, created date NOT NULL, aktivnost_od date NOT NULL, aktivnost_do date NOT NULL, cas_od time NOT NULL, cas_do time NOT NULL, PRIMARY KEY(id));

sed -i "127i\\\n\/\* <<\code>> \*\/\\n\\t\\tdefine('TICKET_TIME_TABLE',\$prefix.'ticket_time');\\n\/\* <<\code>>\/\*" /var/www/html/helpdesk/bootstrap.php

sed -i "127i\\\n\/\* <<\code>> \*\/\\n\\t\\tdefine('TICKET_TIME_TABLE',\$prefix.'ticket_time');\\n\/\* <<\code>>\/\*" /var/www/html/helpdesk/include/client/view.inc.php

echo Konec skripte