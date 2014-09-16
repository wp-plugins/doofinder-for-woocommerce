<?php
if ( ! defined( 'ABSPATH' ) ) exit;

@ob_clean();
header( 'Content-type: application/json' );

$feed = new WC_Doofinder_Config();
echo $feed->render();
exit;