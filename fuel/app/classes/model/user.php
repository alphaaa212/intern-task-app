<?php
use Orm\Model;

class Model_User extends Model
{
    protected static $_connection = 'default';

    protected static $_table_name = 'users';

    protected static $_primary_key = array('id');

    protected static $_properties = array(
        'id',
        'username',
        'email',
        'password_hash',
        'created_at',
        'updated_at',
    );

    protected static $_has_many = array(
        'ideas' => array(
            'key_from' => 'id',
            'model_to' => 'Model_IdeaSelection',
            'key_to' => 'user_id',
            'cascade_save' => true,
            'cascade_delete' => false,
        )
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