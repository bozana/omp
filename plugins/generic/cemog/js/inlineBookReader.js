/**
 * @file plugins/generic/cemog/js/inlineBookReader.js
 *
 * Copyright (c) 2015 CeDis, Freie Universität Berlin
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Functions for inline Bookreader render and viewing.
 */

/**
* Initialize the reading tool javascript 
*/
$(document).ready(function(){

	// 
	// This file shows the minimum you need to provide to BookReader to display a book
	//
	// Copyright(c)2008-2009 Internet Archive. Software license AGPL version 3.

	// Create the BookReader object
	br = new BookReader();
	
	br.mode = br.constMode2up;

	// Return the width of a given page.  Here we assume all images are 800 pixels wide
	br.getPageWidth = function(index) {
	    return 600;
	}

	// Return the height of a given page.  Here we assume all images are 1200 pixels high
	br.getPageHeight = function(index) {
	    return 900;
	}

	// We load the images from archive.org -- you can modify this function to retrieve images
	// using a different URL structure
	br.getPageURI = function(index, reduce, rotate) {
	    // reduce and rotate are ignored in this simple implementation, but we
	    // could e.g. look at reduce and load images from a different directory
	    // or pass the information to an image server
	    var leafStr = '_';            
	    var imgStr = (index > -1)? (index+1).toString(): "";
	    var re = new RegExp("_$");
	    var l_imgBaseUrl = imgBaseUrl;
	    var url = l_imgBaseUrl +'?img='+leafStr.replace(re, imgStr);
	    //var url = 'http://localhost:9076/srv/cemog-book-1/cemog-buch'+leafStr.replace(re, imgStr) + '.jpg';
	    return url;
	}

	// Return which side, left or right, that a given page should be displayed on
	br.getPageSide = function(index) {
	    if (0 == (index & 0x1)) {
	        return 'R';
	    } else {
	        return 'L';
	    }
	}

	// This function returns the left and right indices for the user-visible
	// spread that contains the given index.  The return values may be
	// null if there is no facing page or the index is invalid.
	br.getSpreadIndices = function(pindex) {   
	    var spreadIndices = [null, null]; 
	    if ('rl' == this.pageProgression) {
	        // Right to Left
	        if (this.getPageSide(pindex) == 'R') {
	            spreadIndices[1] = pindex;
	            spreadIndices[0] = pindex + 1;
	        } else {
	            // Given index was LHS
	            spreadIndices[0] = pindex;
	            spreadIndices[1] = pindex - 1;
	        }
	    } else {
	        // Left to right
	        if (this.getPageSide(pindex) == 'L') {
	            spreadIndices[0] = pindex;
	            spreadIndices[1] = pindex + 1;
	        } else {
	            // Given index was RHS
	            spreadIndices[1] = pindex;
	            spreadIndices[0] = pindex - 1;
	        }
	    }
	    
	    return spreadIndices;
	}

	// For a given "accessible page index" return the page number in the book.
	//
	// For example, index 5 might correspond to "Page 1" if there is front matter such
	// as a title page and table of contents.
	br.getPageNum = function(index) {
	    return index+1;
	}

	// Total number of leafs
	br.numLeafs = numLeafs;

	// Book title and the URL used for the book title link
	br.bookTitle = bookTitle;
	br.bookUrl = bookUrl;
	br.bookAuthor = bookAuthor;
	br.logoURL = baseUrl

	// Override the path used to find UI images
	br.imagesBaseURL = '../BookReader/images/';

	br.getEmbedCode = function(frameWidth, frameHeight, viewParams) {
	    return "Embed code not supported in bookreader demo.";
	}

	
	br.buildInfoDiv = function(jInfoDiv) {
		jInfoDiv.find('.BRfloatTitle a').attr({'href': this.bookUrl, 'alt': this.bookTitle}).text(this.bookTitle);
		jInfoDiv.find('.BRfloatAuthorName').append(this.bookAuthor);
		jInfoDiv.find('.BRfloatAbstract').append($("#BookReaderInfoAbstract").html());
	}	
	
	br.blankInfoDiv = function() {
	    return $([
	        '<div class="BRfloat" id="BRinfo">',
	            '<div class="BRfloatHead">About this book',
	                '<a class="floatShut" href="javascript:;" onclick="$.fn.colorbox.close();"><span class="shift">Close</span></a>',
	            '</div>',
	            '<div class="BRfloatBody">',
	                '<div class="BRfloatMeta">',
	                	'<div class="BRfloatTitle">',
	                		'<h3><a/></h3>',
	                		'<div class="BRfloatAuthorName"></div>',
	                	'</div>',
	                    '<div class="BRfloatAbstract"></div>',
	                '</div>',
	            '</div>',
	            '<div class="BRfloatFoot">',
	                '<a href="https://openlibrary.org/dev/docs/bookreader">About the BookReader</a>',
	            '</div>',
	        '</div>'].join('\n')
	    );
	}	

	br.buildShareDiv = function(jShareDiv) {
		// Hide share button
		$('#BRtoolbar .share').hide(); 
		jShareDiv.find('.BRfloatShare').append($("#BookReaderShare").html());
	}	
	
	br.blankShareDiv = function() {		
	    return $([
	        '<div class="BRfloat" id="BRshare">',
	              '<div class="BRfloatHead">Share',
	                  '<a class="floatShut" href="javascript:;" onclick="$.fn.colorbox.close();"><span class="shift">Close</span></a>',
	              '</div>',
	              '<div class="BRfloatBody">',
	              	'<div class="BRfloatShare">',
	              	'</div>',
		          '</div>',
		    '</div>'].join('\n')
	    );
	}
	
	// Let's go!
	br.init();

	// read-aloud and search need backend components and are not supported in the demo
	$('#BRtoolbar').find('.read').hide();
	$('#textSrch').hide();
	$('#btnSrch').hide();

});
