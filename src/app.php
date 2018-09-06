<?php
namespace cameron\movies;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require './vendor/autoload.php';
class App{
  private $app;
  public function __construct($db) {
    $config['db']['host']   = 'localhost';
    $config['db']['user']   = 'root';
    $config['db']['pass']   = 'root';
    $config['db']['dbname'] = '80smovies';
    $app = new \Slim\App(['settings' => $config]);
    $container = $app->getContainer();
    $container['db'] = $db;
    $container['logger'] = function($c) {
        $logger = new \Monolog\Logger('my_logger');
        $file_handler = new \Monolog\Handler\StreamHandler('./logs/app.log');
        $logger->pushHandler($file_handler);
        return $logger;
    };
    // $app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    //     $name = $args['name'];
    //     $this->logger->addInfo('get request to /hello/'.$name);
    //     $response->getBody()->write("Hello, $name");
    //
    //     return $response;
    // });
    $app->get('/movies', function (Request $request, Response $res){
      $this->logger->addInfo("get /movies'");
      $movies = $this->db->query('SELECT * from movies')->fetchAll();
      $jsonrRes - $res->withJson($movies);
      return $jsonRes;
    });
    $app->get('/movies/{id}', function (Request $request, Response $response, array $args) {
      $id = $args['id'];
      $this->logger->addInfo("get /movies".$id);
      $movies = $this->db->query('SELECT * from movies where id ='.$id)->fetch();
      if($movies){
        $response = $response->withJson($movies);
      } else {
        $errorData = array('status' => 404, 'message' => 'not found');
        $response = $response->withJson($errorData, 404);
      }
      return $response;
    });
    $app->put('/movies/{id}', function (Request $request, Response $response, array $args) {
        $id = $args['id'];
        $this->logger->addInfo("PUT /people/".$id);
        // check that movie exists
        $movies = $this->db->query('SELECT * from movies where id='.$id)->fetchAll();
        if(!$movies){
          $errorData = array('status' => 404, 'message' => 'not found');
          $response = $response->withJson($errorData, 404);
          return $response;
        }
        // build query string
        $updateString = "UPDATE movies SET ";
        $fields = $request->getParsedBody();
        $keysArray = array_keys($fields);
        $last_key = end($keysArray);
        foreach($fields as $field => $value) {
          $updateString = $updateString . "$field = '$value'";
          if ($field != $last_key) {
            // conditionally add a comma to avoid sql syntax problems
            $updateString = $updateString . ", ";
          }
        }
        $updateString = $updateString . " WHERE id = $id;";
        // execute query
        try {
          $this->db->exec($updateString);
        } catch (\PDOException $e) {
          $errorData = array('status' => 400, 'message' => 'Invalid data provided to update');
          return $response->withJson($errorData, 400);
        }
        // return updated record
        $movies = $this->db->query('SELECT * from movies where id='.$id)->fetch();
        $jsonResponse = $response->withJson($movies);
        return $jsonResponse;
    });
    $app->delete('/movies/{id}', function (Request $request, Response $response, array $args) {
      $id = $args['id'];
      $this->logger->addInfo("DELETE /movies/".$id);
      $deleteSuccessful = $this->db->exec('DELETE FROM movies where id='.$id);
      if($deleteSuccessful){
        $response = $response->withStatus(200);
      } else {
        $errorData = array('status' => 404, 'message' => 'not found');
        $response = $response->withJson($errorData, 404);
      }
      return $response;
    });
    $this->app = $app;
    }
    /**
    * Get an instance of the application.
    *
    * @return \Slim\App
    */
  public function get()
  {
    return $this->app;
  }
}
