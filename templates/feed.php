<?php
if ( ! defined( 'ABSPATH' ) ) exit;

@ob_clean();
header( 'Content-type: application/xml' );

$feed = new WC_Doofinder_Feed();
echo $feed->render();
exit;