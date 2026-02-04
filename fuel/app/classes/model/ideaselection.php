<?php

class Model_IdeaSelection extends \Orm\Model
{
    protected static $_table_name = 'idea_selections';

    protected static $_primary_key = array('id');

    protected static $_properties = array(
        'id' => array(
            'data_type' => 'int',
            'label' => 'ID',
            'primary' => true,
            'auto_inc' => true,
        ),
        'user_id',
        'idea_text',
        'is_favorite',
        'created_at',
        'updated_at',
    );

    protected static $_belongs_to = array(
        'user' => array(
            'model_to' => 'Model_User',
            'key_from' => 'user_id',
            'key_to'   => 'id',
            'cascade_save' => false,
            'cascade_delete' => false,
        ),
    );

    protected static $_observers = array(
        'Orm\\Observer_CreatedAt' => array(
            'events' => array('before_insert'),
            'mysql_timestamp' => true,
        ),
        'Orm\\Observer_UpdatedAt' => array(
            'events' => array('before_save'),
            'mysql_timestamp' => true,
        ),
    );
}
