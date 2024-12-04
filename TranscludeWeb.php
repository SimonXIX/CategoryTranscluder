<?php

class TranscludeWeb {
    public static function onParserFirstCallInit(Parser $parser): bool {
        $parser->setHook('transclude', [self::class, 'renderTranscludeTag']);
        return true;
    }

    public static function renderTranscludeTag($input, array $args, Parser $parser, PPFrame $frame): string {
        if (empty($args['url'])) {
            return '<span class="error">Error: The "url" attribute is required.</span>';
        }
    
        $url = $args['url'];
        $sections = $args['section'] ?? null;
        $content = self::fetchSections($url, $sections);
    
        if ($content === false) {
            return '<span class="error">Error: Unable to fetch content from the URL or specified sections not found.</span>';
        }
    
        // Parse the sanitized content through MediaWiki's parser
        return $parser->recursiveTagParse($content, $frame);
    }    

    private static function fetchSections(string $url, ?string $sections): string|false {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MediaWiki/1.0 (TranscludeWeb Extension)');

        $html = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($html === false) {
            wfDebugLog('TranscludeWeb', "Error fetching URL $url: $error");
            return false;
        }

        // Parse HTML and extract the specified sections
        return self::extractSectionsFromHTML($html, $sections);
    }

    private static function extractSectionsFromHTML(string $html, ?string $sections): string {
        $dom = new DOMDocument();
        @$dom->loadHTML($html); // Suppress warnings for invalid HTML

        if (!$sections) {
            // No sections specified, return plain text of the entire page
            return self::extractTextFromHTML($html);
        }

        $xpath = new DOMXPath($dom);
        $selectors = array_map('trim', explode(',', $sections)); // Split by commas and trim whitespace
        $combinedContent = '';

        foreach ($selectors as $selector) {
            $nodeList = $xpath->query($selector);
            if ($nodeList->length > 0) {
                foreach ($nodeList as $node) {
                    $combinedContent .= $dom->saveHTML($node);
                }
            }
        }

        if (empty($combinedContent)) {
            // No matching sections found
            return false;
        }

        // Extract plain text from the combined HTML content
        return self::extractTextFromHTML($combinedContent);
    }

    private static function extractTextFromHTML(string $html): string {
        // Remove scripts, styles, and comments
        $html = preg_replace('/<script.*?>.*?<\/script>/is', '', $html);
        $html = preg_replace('/<style.*?>.*?<\/style>/is', '', $html);
        $html = preg_replace('/<!--.*?-->/s', '', $html);
    
        // Allow paragraph tags but remove other tags
        $allowedHtml = strip_tags($html, '<p><blockquote><hr><h1><h2><h3><h4><h5><h6>');
    
        // Collapse excessive whitespace and trim
        $allowedHtml = preg_replace('/\s+/', ' ', $allowedHtml);
    
        return trim($allowedHtml);
    }
    
}


?>