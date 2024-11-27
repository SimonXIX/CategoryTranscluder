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
        $content = self::fetchPlainText($url);

        if ($content === false) {
            return '<span class="error">Error: Unable to fetch content from the URL.</span>';
        }

        // Escape the output to ensure no HTML tags are included
        $output = htmlspecialchars($content);

        // Optionally parse the escaped text through MediaWiki's parser
        return $parser->recursiveTagParse($output, $frame);
    }

    /**
     * Fetches plain text content from a webpage.
     *
     * @param string $url
     * @return string|false The plain text content, or false on failure.
     */
    private static function fetchPlainText(string $url) {
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

        // Strip all HTML tags to get plain text
        $plainText = self::extractTextFromHTML($html);

        return $plainText;
    }

    /**
     * Strips HTML tags and returns clean plain text.
     *
     * @param string $html
     * @return string Plain text content.
     */
    private static function extractTextFromHTML(string $html): string {
        // Remove script, style, and other non-visible content
        $html = preg_replace('/<script.*?>.*?<\/script>/is', '', $html);
        $html = preg_replace('/<style.*?>.*?<\/style>/is', '', $html);
        $html = preg_replace('/<!--.*?-->/s', '', $html);

        // Strip remaining HTML tags
        $plainText = strip_tags($html);

        // Replace multiple spaces and newlines with a single space
        $plainText = preg_replace('/\s+/', ' ', $plainText);

        return trim($plainText);
    }
}

?>