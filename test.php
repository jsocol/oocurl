<?php
/**
 * Test functions for OOCurl
 * 
 * Run this script from the same directory as OOCurl.php to
 * test your cURL/OOCurl installation.
 * 
 * The structure of the test is essentially this:
 *  # Create a new Curl object.
 *  # Retrieve a URL.
 *  # Parse some data from it.
 *  # If there's a failure at any point, throw an Exception.
 * 
 * This test does require a working internet connection. But if
 * you don't have one, why are you using cURL?
 * 
 * @package OOCurl
 * @author James Socol <me@jamessocol.com>
 * @copyright Copyright (c) 2008, James Socol
 * @version 0.1.0
 * @license http://www.opensource.org/licenses/mit-license.php
 */

/* Copyright (c) 2008 James Socol

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

// Hide the nasty error reporting.
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);

// Try to catch all exceptions for polite error handling
try {
	/**
	 * First step, include the OOCurl.php library
	 */
	include_once 'OOCurl.php';
	
	// If we didn't include the file, the class won't exist
	if ( !class_exists('Curl') ) 
		throw new Exception("The file OOCurl.php could not be found. Please run this file from the same directory and make sure I have read and execute permissions for OOCurl.php.");
	
	/**
	 * Our test Curl object.
	 * @global Curl $curl
	 */
	$curl = new Curl;
	
	// If something happened, (which it shouldn't) throw an exception
	if ( !$curl )
		throw new Exception("I couldn't create a Curl object. Was PHP compiled with cURL?");
	
	/**
	 * Set the URL for $curl to our test location:
	 */
	$curl->url = "http://jamessocol.com/projects/oocurl_test.php";
	
	/**
	 * Set a custom header for the test.
	 */
	$curl->httpheader = array('X-OOCurl-Version: ' . Curl::VERSION);
	
	/**
	 * Execute the HTTP query
	 */
	$response = $curl->exec();
	
	// If there's no response, there was an error.
	if ( !$response )
		throw new Exception("I couldn't fetch the response.");
	
	printf("Single request: Success: %s\n", $response);
	printf("I downloaded %n bytes!\n",$curl->info('size_download'));
	// Now we'll test the parallel processor
	
	/**
	 * Let's fetch Yahoo
	 */
	$y = new Curl("http://www.yahoo.com/");
	
	/**
	 * And let's grab Google, too
	 */
	$g = new Curl("http://www.google.com/");
	
	/**
	 * Create a CurlParallel object
	 */
	$m = new CurlParallel($y, $g);
	
	$m->exec();
	
	if ( strlen($g->fetch()) && strlen($y->fetch()) ) {
		printf("Parallel requests: Success!");
	} else {
		throw new Exception("Could not run in parallel.");
	}
	

} catch (Exception $e) {
	// There was a problem! What happened?
	printf("Oh Noes!\n");
	printf("%s\n", $e->getMessage());
	if ( $curl )
		printf( "cURL error: %s\n", $curl->errno());
}

?>