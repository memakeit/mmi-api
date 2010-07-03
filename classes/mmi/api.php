<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Make API calls to 3rd-party services.
 * This class is an attempt to generalize Abraham Williams Twitter OAuth class.
 *
 * @package     MMI API
 * @author      Me Make It
 * @copyright   (c) 2010 Me Make It
 * @copyright   (c) 2009 Abraham Williams
 * @license     http://www.memakeit.com/license
 * @link        http://github.com/abraham/twitteroauth
 */
abstract class MMI_API extends Kohana_MMI_API
{
    // Service name constants
    const SERVICE_BITLY = 'bitly';
    const SERVICE_BRIGHTKITE = 'brightkite';
    const SERVICE_DELICIOUS = 'delicious';
    const SERVICE_DIGG = 'digg';
    const SERVICE_FACEBOOK = 'facebook';
    const SERVICE_FLICKR = 'flickr';
    const SERVICE_FOURSQUARE = 'foursquare';
    const SERVICE_FRIENDFEED = 'friendfeed';
    const SERVICE_EVERNOTE = 'evernote';
    const SERVICE_GITHUB = 'github';
    const SERVICE_GOODREADS = 'goodreads';
    const SERVICE_GOOGLEBUZZ = 'googlebuzz';
    const SERVICE_GOWALLA = 'gowalla';
    const SERVICE_LASTFM = 'lastfm';
    const SERVICE_LINKEDIN = 'linkedin';
    const SERVICE_MIXX = 'mixx';
    const SERVICE_MYSPACE = 'myspace';
    const SERVICE_PICASA = 'picasa';
    const SERVICE_READERNAUT = 'readernaut';
    const SERVICE_REDDIT = 'reddit';
    const SERVICE_SCRIBD = 'scribd';
    const SERVICE_SLIDESHARE = 'slideshare';
    const SERVICE_SOUNDCLOUD = 'soundcloud';
    const SERVICE_TWITTER = 'twitter';
    const SERVICE_VIMEO = 'vimeo';
    const SERVICE_YOUTUBE = 'youtube';
    const SERVICE_ZOOTOOL = 'zootool';
}