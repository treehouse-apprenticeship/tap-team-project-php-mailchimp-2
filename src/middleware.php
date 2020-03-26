<?php
// Application middleware

// Register csrf for all routes
$app->add($container->get('csrf'));