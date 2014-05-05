<?php

$string['mediasite'] = 'Mediasite';

$string['modulename'] = 'Mediasite Content';
$string['modulenameplural'] = 'Mediasite Content';
$string['pluginname'] = 'Mediasite Content';

$string['mediasitepresentation'] = 'Presentation';
$string['mediasitecatalog'] = 'Catalog';
$string['mediasitenotauthorized'] = 'You are not authorized for this resource.';
$string['mediasitenotfound'] = 'The selected Mediasite content was not found.';


//mod_form.php
$string['mediasitename'] = 'Title';
$string['mediasiteresourcetype'] = 'Content Type';
$string['mediasiteresourceid'] = 'Resource Id';
$string['mediasitesearchbutton'] = 'Search for Mediasite content';
$string['mediasiteopenaspopup'] = 'Open in popup window';

//settings.php
$string['mediasiteserverurl'] = 'Mediasite Server';
$string['mediasiteserverurldescription'] = 'The URL for the Mediasite server including the Mediasite root virtual directory.';
$string['mediasiteticketduration'] = 'Ticket duration';
$string['mediasiteticketdurationdescription'] = 'Length in minutes that generated authorization tickets will be valid.';
$string['mediasiterestricttoip'] = 'Restrict to IP';
$string['mediasiterestricttoipdescription'] = 'Bind authorization tickets to the client IP address to prevent link sharing.  This may need to be disabled when using a CDN or if the Moodle and Mediasite servers are on different networks.';
$string['mediasitepassthru'] = 'Passthru Authentication';
$string['mediasitepassthrudescription'] = 'Enable "passthru" authentication. This means that there is the same user name that is known to Moodle and a local authentication server (eg. LDAP)';
$string['mediasiteusername'] = 'Username';
$string['mediasiteusernamedescription'] = 'Admin or system user on the Mediasite server.';
$string['mediasitepassword'] = 'Password';
$string['mediasitepassworddescription'] = 'Password of the admin or system user.';
$string['mediasiteapikey'] = 'Medisite API Key';
$string['mediasiteapikeydescription'] = 'The API Key for the Moodle plugin.';
$string['mediasiteactive'] = 'Enable';
$string['mediasiteactivedescription'] = 'Flag controlling sites availability.';

//site administration
$string['mediasitesitename'] = 'Site Name';
$string['mediasitesitenamedescription'] = 'An arbitrary name for a Mediasite site';
$string['mediasitesitenamedescriptions'] = 'A list of arbitrary names for Mediasite sites';
$string['mediasitesitenames'] = 'Mediasite Sites';
$string['mediasiteeditchoose'] = 'Edit';
$string['mediasiteaddchoose'] = 'Add';
$string['mediasiteeditconfirm'] = 'Add';
$string['mediasitenosites'] = 'There are no configured sites.';

//search, search_form
$string['mediasitesearchtext'] = 'Search For:';
$string['mediasitesearchsubmit'] = 'Search';
$string['mediasitesearchnoresult'] = 'No results were found matching your search.';
$string['mediasitesearchchoose'] = 'Choose';

// capabilities
$string['mediasite:searchforcontent'] = "Search for Mediasite content";
$string['mediasite:addinstance'] = "Add Mediasite content to a course";

// plugin administration
$string['pluginadministration'] = "Mediasite Content administration";
?>
