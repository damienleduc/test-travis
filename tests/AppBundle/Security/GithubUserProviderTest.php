<?php

namespace Tests\AppBundle\Security;

use AppBundle\Security\GithubUserProvider;
use AppBundle\Entity\User;
use PHPUnit\Framework\TestCase;

class GithubUserProviderTest extends TestCase
{
	private $client;
	private $serializer;
	private $streamedResponse;
	private $response;

	public function setUp()
	{
		$this->client = $this->getMockBuilder('GuzzleHttp\Client')
    		->disableOriginalConstructor()
    		->setMethods(['get'])
    		->getMock();

    	$this->serializer = $this
    		->getMockBuilder('JMS\Serializer\Serializer')
    		->disableOriginalConstructor()
    		->getMock();

    	$this->response = $this
    		->getMockBuilder('Psr\Http\Message\ResponseInterface')
    		->getMock();

    	$this->streamedResponse = $this
    		->getMockBuilder('Psr\Http\Message\StreamInterface')
    		->getMock();
	}

    public function testLoadUserByUsernameReturningAUser()
    {
    	$this->client
    		->expects($this->once())
    		->method('get')->willReturn($this->response);

    	$this->response
    		->expects($this->once())
    		->method('getBody')->willReturn($this->streamedResponse);

    	$userData = [
    		'login' => '',
    		'name' => '',
    		'email' => '',
    		'avatar_url' => '',
    		'html_url' => '',
    	];

    	$this->serializer
    		->expects($this->once())
    		->method('deserialize')->willReturn($userData);

    	$githubUserProvider = new GithubUserProvider($this->client, $this->serializer);

    	$expectedUser = new User($userData['login'], $userData['name'], $userData['email'], $userData['avatar_url'], $userData['html_url']);
    	$user = $githubUserProvider->loadUserByUserName('user');

    	$this->assertInstanceOf('AppBundle\Entity\User', $user);
    	$this->assertEquals($expectedUser, $user);
    }

    public function testLoadUserByUsernameThrowingError()
    {
    	
    	$this->client
    		->method('get')->willReturn($this->response);

    	

    	$this->response
    		->method('getBody')->willReturn($this->streamedResponse);

    	$userData = [];

    	$this->serializer
    		->method('deserialize')->willReturn($userData);

    	$githubUserProvider = new GithubUserProvider($this->client, $this->serializer);

    	$this->expectException(\LogicException::class);

    	$user = $githubUserProvider->loadUserByUserName('user');
    }

    public function tearDown()
    {
        $this->client = null;
        $this->serializer = null;
        $this->streamedResponse = null;
        $this->response = null;
    }
}
