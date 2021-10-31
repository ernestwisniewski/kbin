<?php declare(strict_types = 1);

namespace App\Utils;

use App\Exception\BadUrlException;

class UrlCleaner
{
    // https://gist.github.com/htsign/455bd76d107be1f810c5caa4072c8275
    const TRACKING_TAGS = [
        'utm_source',
        'utm_medium',
        'utm_term',
        'utm_content',
        'utm_campaign',
        'utm_reader',
        'utm_place',
        'utm_userid',
        'utm_cid',
        'utm_name',
        'utm_pubreferrer',
        'utm_swu',
        'utm_viz_id',
        'utm_int',
        'ga_source',
        'ga_medium',
        'ga_term',
        'ga_content',
        'ga_campaign',
        'ga_place',
        'yclid, _openstat',
        'fb_action_ids',
        'fb_action_types',
        'fb_ref',
        'fb_source',
        'action_object_map',
        'action_type_map',
        'action_ref_map',
        'gs_l',
        'pd_rd_*@amazon.*',
        '_encoding@amazon.*',
        'psc@amazon.*',
        'ei@google.*',
        'bi?@google.*',
        'client@google.*',
        'dpr@google.*',
        'gws_rd@google.*',
        'oq@google.*',
        'sa@google.*',
        'sei@google.*',
        'source@google.*',
        'tbm@google.*',
        'ved@google.*',
        'cvid@bing.com',
        'form@bing.com',
        'sk@bing.com',
        'sp@bing.com',
        'sc@bing.com',
        'qs@bing.com',
        'pq@bing.com',
        'feature@youtube.com',
        'gclid@youtube.com',
        'kw@youtube.com',
        'gws_rd',
        'hmb_campaign',
        'hmb_medium',
        'hmb_source',
        '_hsmi',
        'ref_src',
        'ref_url',
        'source@sourceforge.net',
        'position@sourceforge.net',
        'callback@bilibili.com',
        'ref@www.asahi.com',
        'iref@www.asahi.com',
        'rm@digital.asahi.com',
        'word_result@nhk.or.jp',
        'algorithm@www.change.org',
        'grid_position@www.change.org',
        'j@www.change.org',
        'jb@www.change.org',
        'mid@www.change.org',
        'l@www.change.org',
        'original_footer_petition_id@www.change.org',
        'placement@www.change.org',
        'pt@www.change.org',
        'sfmc_sub@www.change.org',
        'source_location@www.change.org',
        'u@www.change.org',
        'n_cid@nikkeibp.co.jp',
        'fbclid@itmedia.co.jp',
        'ref@*.nicovideo.jp',
        '#?utm_medium',
        '#?utm_source',
        '#?utm_campaign',
        '#?utm_content',
        '#?utm_int',
        'fbclid',
    ];

    public function __invoke(string $url): string
    {
        foreach (self::TRACKING_TAGS as $tag) {
            $url = $this->removeVar($url, $tag);
        }

        return $url;
    }

    private function removeVar(string $url, string $var): string
    {
        [$urlPart, $qsPart] = array_pad(explode('?', $url), 2, '');
        parse_str($qsPart, $qsVars);
        unset($qsVars[$var]);
        $newQs = http_build_query($qsVars);

        return $this->validate(trim($urlPart.'?'.$newQs, '?'));
    }

    private function validate(string $url): string
    {
        // @todo checkdnsrr?
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new BadUrlException($url);
        }

        return $url;
    }
}
