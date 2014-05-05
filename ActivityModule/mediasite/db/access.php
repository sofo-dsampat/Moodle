<?php
$capabilities = array(
  'mod/mediasite:searchforcontent' => array (
  	'riskbitmask' => RISK_PERSONAL,
  	'captype' => 'read',
  	'contextlevel' => CONTEXT_COURSE,
  	'legacy' => array (
  	     'student' => CAP_ALLOW,
             'teacher' => CAP_ALLOW,
  	     'editingteacher' => CAP_ALLOW,            
  	     'manager' => CAP_ALLOW
  	)
  ),
  'mod/mediasite:addinstance' => array (
      'riskbitmask' => RISK_PERSONAL,
      'captype' => 'write',
      'contextlevel' => CONTEXT_COURSE,
      'legacy' => array (
              'student' => CAP_ALLOW,
              'teacher' => CAP_ALLOW,
              'editingteacher' => CAP_ALLOW,
              'manager' => CAP_ALLOW
            )
  )
)
?>