<?php

namespace UserFrosting;

use Illuminate\Database\Eloquent\Model;

use \Illuminate\Database\Capsule\Manager as Capsule;

/**
 * UFModel Class
 *
 * The base Eloquent data model, from which all UserFrosting data classes extend.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @see MySqlDatabase
 */
abstract class UFModel extends Model {
    
    /**
     * @var Slim The Slim app, containing configuration info
     */
    public static $app;
    
    /**
     * @var string The id of the table for the current model.  Must be overridden in child class.
     */ 
    protected static $_table_id = "";
    
    /**
     * @var bool Disable timestamps for now.
     */ 
    public $timestamps = false;
    
    /**
     * Create a new object, initializing the table name and whitelisted columns.
     *
     */
    public function __construct($properties = [], $id = null) {    
        $table_schema = Database::getSchemaTable(static::$_table_id);
        $this->table = $table_schema->name;
        $this->fillable = $table_schema->columns;
        parent::__construct($properties);
    }
    
    // For raw array fetching.  Must be static, otherwise PHP gets confused about where to find the table_id.
    public static function queryBuilder(){
        // Set query builder to fetch result sets as associative arrays (instead of creating stdClass objects)
        Capsule::connection()->setFetchMode(\PDO::FETCH_ASSOC);
        $table = Database::getSchemaTable(static::$_table_id)->name;
        return Capsule::table($table);
    }    
    
    // For excluding certain columns in a query
    public function scopeExclude($query, $value = []) {
        $columns = array_merge(['id'], Database::getSchemaTable(static::$_table_id)->columns);
        return $query->select( array_diff( $columns,(array) $value) );
    }
    
    /**
     * Calls save(), then returns the id of the new record in the database.
     *
     * @see DatabaseInterface
     */ 
    public function store(){        
        $this->save();
        
        // Store function should always return the id of the object
        return $this->id;        
    }
    
    public function export(){
        return $this->toArray();
    } 
}