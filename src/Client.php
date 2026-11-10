<?php

namespace OdooPHP;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use OdooPHP\BaseClient;
use OdooPHP\OdooException;

class Client extends BaseClient
{
//    public function __construct(string $baseUri, protected string $apiKey, protected ?string $database = null, bool $sslVerify = true)
//    public function call(string $model, string $method, array $params = []): mixed
//    public function lastResponse(): ?ResponseInterface
//    public function getLastResponseContents(): ?string
//    private function makeResponse(ResponseInterface $response): mixed

	// for operations that read data (ex: search_read, read), if this is false, the fields that reference other objects will not be expanded to include the display_name of that object
	// if false, fields will contain just the numeric id, when true (default), fields will be [ 0 => numeric id, 1 => display_name]
	private bool $compute_fields = true;
	
	/**
	* Set the compute fields property. If set to false, object ids will not be expanded into an array containing the id and display_name
	**/
	public function set_compute_fields(bool $new_value): self
		{
		$this->compute_fields = $new_value;
		
		return $this;
		}

	/**
	* Search for records using the specified filter with optional limits and sorting
	* @return array of integer - A list of integer ids for the identified records
	**/
	public function search(string $model, array $filter, ?int $limit = null, ?int $offset = null, ?string $order = null, ?array $context = null): array
		{
		$params = [];
		$params['domain']	= $filter;
		if (!is_null($limit))	{ $params['limit'] = $limit; }
		if (!is_null($offset))	{ $params['offset'] = $offset; }
		if (!is_null($order))	{ $params['order'] = $order; }
		if (!is_null($context))	{ $params['context'] = $context; }
		
		return $this->call($model, 'search', $params);
		}
	
	/**
	* Search for records using the specified filter with optional limit
	* @return int - how many records it found
	**/
	public function count(string $model, array $filter, ?int $limit = null, ?array $context = null): int
		{
		$params = [];
		$params['domain']	= $filter;
		if (!is_null($limit))	{ $params['limit'] = $limit; }
		if (!is_null($context))	{ $params['context'] = $context; }
		
		return $this->call($model, 'search_count', $params);
		}
	
	/**
	* Search for records using the specified filter with optional limits and sorting
	* @return array - return the records and their data as found
	**/
	public function search_read(string $model, array $filter, ?array $fields = null, ?int $limit = null, ?int $offset = null, ?string $order = null, ?array $context = null): array
		{
		$params = [];
		$params['domain']	= $filter;
		if (!is_null($fields))	{ $params['fields'] = $fields; }
		if (!is_null($limit))	{ $params['limit'] = $limit; }
		if (!is_null($offset))	{ $params['offset'] = $offset; }
		if (!is_null($order))	{ $params['order'] = $order; }
		if (!$this->compute_fields)	{ $params['load'] = false; }
		if (!is_null($context))	{ $params['context'] = $context; }
		
		return $this->call($model, 'search_read', $params);
		}

	/**
	* Read the records for the specified id list
	* @return array - records coresponding to the specified ids
	**/
	public function read(string $model, array $ids = [], ?array $fields = null, ?array $context = null): array
		{
		$params = [];
		$params['ids']	= $ids;
		if (!is_null($fields))	{ $params['fields'] = $fields; }
		if (!$this->compute_fields)	{ $params['load'] = false; }
		if (!is_null($context))	{ $params['context'] = $context; }
		
		return $this->call($model, 'read', $params);
		}

	/**
	* Create a new record with the specified values
	* @return array - the created record(s) id(s)
	**/
	public function create(string $model, array $values, ?array $context = null): array
		{
		$params = [];
		$params['vals_list']	= $values;
		if (!is_null($context))	{ $params['context'] = $context; }
		
		return $this->call($model, 'create', $params);
		}

	/**
	* Update the specified records with the specified values
	* @return bool - true if write succeded
	**/
	public function write(string $model, array $ids, array $values, ?array $context = null): bool
		{
		$params = [];
		$params['ids']	= $ids;
		$params['vals']	= $values;
		if (!is_null($context))	{ $params['context'] = $context; }
		
		return $this->call($model, 'write', $params);
		}

	/**
	* Delete the specified records
	* @return bool - true if delete succeded
	**/
	public function unlink(string $model, array $ids, ?array $context = null): bool
		{
		$params = [];
		$params['ids']	= $ids;
		if (!is_null($context))	{ $params['context'] = $context; }
		
		return $this->call($model, 'unlink', $params);
		}

	/**
	* Get the fields available for the specified model with their information
	* @return array - the fields list
	**/
	public function get_fields(string $model, ?array $fields = null, ?array $attributes = null, ?array $context = null): array
		{
		$params = [];
		if (!is_null($fields))	{ $params['fields'] = $fields; }
		if (!is_null($attributes))	{ $params['attributes'] = $attributes; }
		if (!is_null($context))	{ $params['context'] = $context; }
		
		return $this->call($model, 'fields_get', $params);
		}

	/**
	* Get the default values for the specified fields
	* @return array - only the fields with default values are returned
	**/
	public function get_fields_defaults(string $model, ?array $fields = null, ?array $context = null): array
		{
		$params = [];
		$params['fields'] = $fields;
		if (!is_null($context))	{ $params['context'] = $context; }
		
		return $this->call($model, 'default_get', $params);
		}

	/**
	* Check if the user has permission to perform any of the "create", "read", "write", "unlink" operations
	* @return bool
	**/
	public function has_access(string $model, string $operation, ?array $attributes = null, ?array $context = null): bool
		{
		$params = [];
		$params['ids']	= $ids;
		$params['operation'] = $operation;
		if (!is_null($context))	{ $params['context'] = $context; }
		
		return $this->call($model, 'has_access', $params);
		}

	/**
	* Mark the specified ids as inactive
	* @return null
	**/
	public function archive(string $model, array $ids, ?array $context = null): null
		{
		$params = [];
		$params['ids']	= $ids;
		if (!is_null($context))	{ $params['context'] = $context; }
		
		return $this->call($model, 'action_archive', $params);
		}

	/**
	* Mark the specified ids as active
	* @return null
	**/
	public function unarchive(string $model, array $ids, ?array $context = null): null
		{
		$params = [];
		$params['ids']	= $ids;
		if (!is_null($context))	{ $params['context'] = $context; }
		
		return $this->call($model, 'action_unarchive', $params);
		}

	/**
	* Get the metadata associated with the specified records
	* @return array
	**/
	public function get_metadata(string $model, array $ids, ?array $context = null): array
		{
		$params = [];
		$params['ids']	= $ids;
		if (!is_null($context))	{ $params['context'] = $context; }
		
		return $this->call($model, 'get_metadata', $params);
		}


}

