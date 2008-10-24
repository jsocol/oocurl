<?php
/**
 * OOCurl
 * 
 * Provides an Object-Oriented interface to the PHP cURL
 * functions and clean up some of the curl_setopt() calls.
 *
 * Instead of requiring a setopt() function and the CURLOPT_*
 * constants, which are cumbersome and ugly at best, this object
 * implements curl_setopt() through overloaded getter and setter
 * methods.
 *
 * For example, if you wanted to include the headers in the output,
 * the old way would be
 * <code>
 * curl_setopt($ch, CURLOPT_HEADER, true);
 * </code>
 * But with this object, it's simply
 * <code>
 * $ch->header = true;
 * </code>
 * 
 * NB: Since, in my experience, the vast majority if cURL scripts
 * set CURLOPT_RETURNTRANSFER to true, this Class sets it by
 * default. If you do not want CURLOPT_RETURNTRANSFER, you'll need
 * to do this:
 * <code>
 * $c = new Curl;
 * $c->returntransfer = false;
 * </code>
 * 
 * @todo Things to consider...
 *      - Adding support for parallel processing somehow.
 *        Maybe a CurlParallel class? Use Visitor to get
 *        around protected variables?
 *      - Add support for curl_getinfo().
 *      - Add support for CURLINFO_* constants.
 *      - Add support for curl_setopt_array() via {@link __set()}
 *      - Consider adding $curlopt_default array to implement
 *        {@link __unset()} for real.
 *
 * 
 * 
 * @package OOCurl
 * @author James Socol <me@jamessocol.com>
 * @version 0.1.0
 * @copyright 2008 James Socol
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
 
/**
 * Curl object
 *
 * Provides an Object-Oriented interface to the PHP cURL
 * functions and a clean way to replace curl_setopt().
 *
 * @package OOCurl
 */
class Curl
{
	/**
	 * Store the curl_init resource.
	 */
	protected $ch = NULL;

	/**
	 * Store the CURLOPT_* values.
	 * 
	 * Do not access directly. Access through {@link __get()} 
	 * and {@link __set()}.
	 */
	protected $curlopt = array();
	
	/**
	 * Create the new Curl object, with the
	 * optional URL parameter.
	 *
	 * @param string $url optional
	 * @throws ErrorException
	 */
	public function __construct ( $url = NULL )
	{
		// Make sure the cURL extension is loaded
		if ( !extension_loaded('curl') ) 
			throw new ErrorException("cURL library is not loaded. Please recompile PHP with the cURL library.");
		
		// Create the cURL resource
		$this->ch = curl_init();
		
		// Set some default options
		$this->url = $url;
		$this->returntransfer = true;
		
		// Return $this for chaining
		return $this;
	}
	
	/**
	 * Execute the cURL transfer.
	 *
	 * @return mixed
	 */
	public function exec ()
	{
		return curl_exec($this->ch);
	}
	
	/**
	 * Return an error string from the last execute (if any).
	 *
	 * @return string
	 */
	public function error()
	{
		return curl_error($this->ch);
	}
	
	/**
	 * Return the error number from the last execute (if any).
	 *
	 * @return integer
	 */
	public function errno()
	{
		return curl_errno($this->ch);
	}
	
	/**
	 * Overloaded set.
	 *
	 * A sneaky way to access curl_setopt(). If the
	 * constant CURLOPT_$opt exists, then we try to set
	 * the option using curl_setopt() and return its
	 * success. If it doesn't exist, just return false.
	 *
	 * @param string $opt
	 * @param mixed $value
	 * @return bool
	 */
	public function __set ( $opt, $value )
	{
		$const = 'CURLOPT_'.strtoupper($opt);
		if ( defined($const) ) {
			if (curl_setopt($this->ch,
							constant($const),
							$value)) {
				$this->curlopt[$const] = $value;
				return true;
			}
		} 
		
		return false;
	}
	
	/**
	 * Overloaded get.
	 * 
	 * When options are set with {@link __set()}, they
	 * are also stored in {@link $curlopts} so that we
	 * can always find out what the options are now.
	 * 
	 * The default cURL functions lack this ability.
	 *
	 * @param string $opt
	 * @return mixed
	 */
	public function __get ( $opt )
	{
		return $this->curlopt['CURLOPT_'.strtoupper($opt)];
	}
	
	/**
	 * Overloaded isset.
	 *
	 * Can tell if a CURLOPT_* value was set by using
	 * <code>
	 * isset($curl-><var>option</var>)
	 * </code>
	 *
	 * The default cURL functions lack this ability.
	 *
	 * @param string $opt
	 * @return bool
	 */
	public function __isset ( $opt )
	{
		return isset($this->curlopt['CURLOPT_'.strtoupper($opt)]);
	}
	
	/**
	 * Overloaded unset
	 *
	 * Unfortunately, there is no way, short of writing an
	 * extremely long, but mostly NULL-filled array, to
	 * implement a decent version of
	 * <code>
	 * unset($curl->option);
	 * </code>
	 *
	 * @todo Consider implementing an array of all the CURLOPT_*
	 *       constants and their default values.
	 * @param string $opt
	 */
	public function __unset ( $opt )
	{
		// Since we really can't reset a CURLOPT_* to its
		// default value without knowing the default value,
		// just return false.
		return false;
	}
}

?>