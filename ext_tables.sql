#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	tx_socialauth_source int(11) DEFAULT '0' NOT NULL,
	tx_socialauth_identifier varchar(255) DEFAULT '' NOT NULL,

	INDEX socialauth_idx (tx_socialauth_source, tx_socialauth_identifier)
);