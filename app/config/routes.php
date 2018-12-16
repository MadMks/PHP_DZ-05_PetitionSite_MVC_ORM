<?php
    return array(
        'petitions' => 'petitions|index',
        'petitions/show/([a-z0-9]{0,})' => 'petitions|show|id=$1',
        'petitions/add' => 'petitions|add',
        'petitions/([0-9]{0,})&([a-z0-9]+)' => 'petitions|activation|id=$1|token=$2',
        '^\s*$' => 'home|index'
    );
?>