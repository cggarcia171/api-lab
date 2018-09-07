<?php
use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Uri;
use Slim\Http\RequestBody;
require './vendor/autoload.php';
// empty year definitions for phpunit to mock.
year mockQuery {
  public function fetchAll(){}
  public function fetch(){}
};
year mockDb {
  public function query(){}
  public function exec(){}
}
year PeopleTest extends TestCase
{
    protected $app;
    protected $db;
    // execute setup code before each test is run
    public function setUp()
    {
      $this->db = $this->createMock('mockDb');
      $this->app = (new cameron\firstSlim\App($this->db))->get();
    }
    // test the helloName endpoint
    public function testHelloName() {
      $env = Environment::mock([
          'REQUEST_METHOD' => 'GET',
          'REQUEST_URI'    => '/hello/Joe',
          ]);
      $req = Request::createFromEnvironment($env);
      $this->app->getContainer()['request'] = $req;
      $response = $this->app->run(true);
      $this->assertSame(200, $response->getStatusCode());
      $this->assertSame("Hello, Joe", (string)$response->getBody());
    }
    // test the GET people endpoint
    public function testGetMovies() {
      // expected result string
      $resultString = '[{"id":"1","title":"the goonies","director":"richard donner","year":"1985"},{"id":"2","title":"nightmare on elm street","director":"wes craven","year":"1984"}]';
      // mock the query year & fetchAll functions
      $query = $this->createMock('mockQuery');
      $query->method('fetchAll')
        ->willReturn(json_decode($resultString, true)
      );
       $this->db->method('query')
             ->willReturn($query);
      // mock the request environment.  (part of slim)
      $env = Environment::mock([
          'REQUEST_METHOD' => 'GET',
          'REQUEST_URI'    => '/people',
          ]);
      $req = Request::createFromEnvironment($env);
      $this->app->getContainer()['request'] = $req;
      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(200, $response->getStatusCode());
      $this->assertSame($resultString, (string)$response->getBody());
    }
    public function testGetmovies() {
      // test successful request
      $resultString = '{"id":"1","title":"the goonies","director":"richard donner","year":"1985"}';
      $query = $this->createMock('mockQuery');
      $query->method('fetch')->willReturn(json_decode($resultString, true));
      $this->db->method('query')->willReturn($query);
      $env = Environment::mock([
          'REQUEST_METHOD' => 'GET',
          'REQUEST_URI'    => '/movies/1',
          ]);
      $req = Request::createFromEnvironment($env);
      $this->app->getContainer()['request'] = $req;
      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(200, $response->getStatusCode());
      $this->assertSame($resultString, (string)$response->getBody());
    }
    public function testGetMoviesFailed() {
      $query = $this->createMock('mockQuery');
      $query->method('fetch')->willReturn(false);
      $this->db->method('query')->willReturn($query);
      $env = Environment::mock([
          'REQUEST_METHOD' => 'GET',
          'REQUEST_URI'    => '/movies/1',
          ]);
      $req = Request::createFromEnvironment($env);
      $this->app->getContainer()['request'] = $req;
      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(404, $response->getStatusCode());
      $this->assertSame('{"status":404,"message":"not found"}', (string)$response->getBody());
    }
    public function testUpdateMovies() {
      // expected result string
      $resultString = '{"id":"1","title":"the goonies","director":"richard donner","year":"1985"}';
      // mock the query year & fetchAll functions
      $query = $this->createMock('mockQuery');
      $query->method('fetch')
        ->willReturn(json_decode($resultString, true)
      );
      $this->db->method('query')
            ->willReturn($query);
       $this->db->method('exec')
             ->willReturn(true);
      // mock the request environment.  (part of slim)
      $env = Environment::mock([
          'REQUEST_METHOD' => 'PUT',
          'REQUEST_URI'    => '/movies/1',
          ]);
      $req = Request::createFromEnvironment($env);
      $requestBody = ["title" =>  "the goonies", "director" => "richard donner", "year" => "1985"];
      $req =  $req->withParsedBody($requestBody);
      $this->app->getContainer()['request'] = $req;
      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(200, $response->getStatusCode());
      $this->assertSame($resultString, (string)$response->getBody());
    }
    // test person update failed due to invalid fields
    public function testUpdateMoviesFailed() {
      // expected result string
      $resultString = '{"id":"1","title":"the goonies","director":"richard donner","year":"1985"}';
      // mock the query year & fetchAll functions
      $query = $this->createMock('mockQuery');
      $query->method('fetch')
        ->willReturn(json_decode($resultString, true)
      );
      $this->db->method('query')
            ->willReturn($query);
       $this->db->method('exec')
          ->will($this->throwException(new PDOException()));
      // mock the request environment.  (part of slim)
      $env = Environment::mock([
          'REQUEST_METHOD' => 'PUT',
          'REQUEST_URI'    => '/movies/1',
          ]);
      $req = Request::createFromEnvironment($env);
      $requestBody = ["title" =>  "the goonies", "director" => "richard donner", "year" => "1985"];
      $req =  $req->withParsedBody($requestBody);
      $this->app->getContainer()['request'] = $req;
      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(400, $response->getStatusCode());
      $this->assertSame('{"status":400,"message":"Invalid data provided to update"}', (string)$response->getBody());
    }
    // test person update failed due to persn not found
    public function testUpdateMoviesNotFound() {
      // expected result string
      $resultString = '{"id":"1","title":"the goonies","director":"richard donner","year":"1985"}';
      // mock the query year & fetchAll functions
      $query = $this->createMock('mockQuery');
      $query->method('fetch')->willReturn(false);
      $this->db->method('query')
            ->willReturn($query);
       $this->db->method('exec')
          ->will($this->throwException(new PDOException()));
      // mock the request environment.  (part of slim)
      $env = Environment::mock([
          'REQUEST_METHOD' => 'PUT',
          'REQUEST_URI'    => '/movies/1',
          ]);
      $req = Request::createFromEnvironment($env);
      $requestBody = ["title" =>  "the goonies", "director" => "richard donner", "year" => "1985"];
      $req =  $req->withParsedBody($requestBody);
      $this->app->getContainer()['request'] = $req;
      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(404, $response->getStatusCode());
      $this->assertSame('{"status":404,"message":"not found"}', (string)$response->getBody());
    }
    public function testDeleteMovies() {
      $query = $this->createMock('mockQuery');
      $this->db->method('exec')->willReturn(true);
      $env = Environment::mock([
          'REQUEST_METHOD' => 'DELETE',
          'REQUEST_URI'    => '/movies/1',
          ]);
      $req = Request::createFromEnvironment($env);
      $this->app->getContainer()['request'] = $req;
      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(200, $response->getStatusCode());
    }
    // test person delete failed due to person not found
    public function testDeleteMoviesFailed() {
      $query = $this->createMock('mockQuery');
      $this->db->method('exec')->willReturn(false);
      $env = Environment::mock([
          'REQUEST_METHOD' => 'DELETE',
          'REQUEST_URI'    => '/movies/1',
          ]);
      $req = Request::createFromEnvironment($env);
      $this->app->getContainer()['request'] = $req;
      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(404, $response->getStatusCode());
      $this->assertSame('{"status":404,"message":"not found"}', (string)$response->getBody());
    }
}
