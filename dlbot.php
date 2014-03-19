<?php
    require_once('phpQuery.php');
    class SiteParser { 
        private $c;   
        
        function __construct() { 
            $this->c = curl_init();
            curl_setopt($this->c, CURLOPT_RETURNTRANSFER, true);
    	    curl_setopt($this->c, CURLOPT_FOLLOWLOCATION, 1);
    	    curl_setopt($this->c, CURLOPT_POST, 0);  
        } 
        
        function __destruct() { 
    	    curl_close($this->c);  
        } 
        
        function match($pattern, $el) {
            preg_match_all($pattern, $el, $matches); 
            if (isset($matches[1])) {
                if (isset($matches[1][0])) {
                    return $matches[1][0];
                }
                return null;
            }
            return null;
        }
    
        function getAttr($el, $attr) {
            $pattern = '#' . $attr . '="([^"]*)"#';  
            return $this->match($pattern, $el);
        }
        
        function getAbsoluteLink($addr, $rel) {
            $i = 0;
            
            while (substr($rel, $i, 3) == '../') {
                $i += 3;
            }
            $i /= 3;
            $k = $i + 1;
            $pos = strlen($addr) - 1;
            while (1) {
            	if ($addr[$pos] == '/') {
            		$k--;
            		if ($k == 0) {
            			break;
            		}
            	}
            	$pos--;
            }
            
            return substr($addr, 0, $pos) . '/' . substr($rel, $i * 3);
        }
        
        function getLinkList($address, $selector, $attribute = 'href') {
            curl_setopt($this->c, CURLOPT_URL, $address);
    		$contents = curl_exec($this->c);
    		$doc = phpQuery::newDocumentHTML($contents);
    		$elements = $doc->find($selector);
    		$result = Array();
    
    		foreach ($elements as $k => $v) {
    		    $link = htmlspecialchars_decode($this->getAttr($elements->eq($k), $attribute));
    		    array_push($result, $link);
    		}
    		return $result;
        }
        
        function getLink($address, $selector, $attribute = 'href') {
    		$result = $this->getLinkList($address, $selector, $attribute);
    		if (count($result) > 0) {
    		    return $result[0];
    		}
    		return null;
        }
        
        function getContent($address, $selector) {
            curl_setopt($this->c, CURLOPT_URL, $address);
    		$contents = curl_exec($this->c);
    		$doc = phpQuery::newDocumentHTML($contents);
    		$elements = $doc->find($selector);

    		if (count($elements) > 0) {
    		    return $elements[0];
    		}
    		return null;
        }
        
        function saveFile($folder, $link) {
            curl_setopt($this->c, CURLOPT_URL, $link);
            $contents = curl_exec($this->c);
            $fname = $this->match('#([^/]+)$#', $link);
            file_put_contents($folder . '/' . $fname, $contents);
        }
    } 
?>