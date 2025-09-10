<?php

namespace App\Http\Middleware;

require_once app_path() . '/Htmlpurifier/HTMLPurifier.auto.php';

use Closure;
use HTMLPurifier;
use HTMLPurifier_AttrDef_Enum;
use HTMLPurifier_AttrDef_Integer;
use HTMLPurifier_Config;
use Illuminate\Http\Request;

class XssSanitization
{
    /**
     * The following method loops through all request input and strips out all tags from
     * the request. This to ensure that users are unable to set ANY XSS vulnerability within
     * the form submissions, but also cleans up input.
     *
     * @param Request $request
     * @param callable $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // skip when request method is other than 'PUT' or 'POST'
        if (!in_array(strtolower($request->method()), ['patch', 'put', 'post'])) {
            return $next($request);
        }

        /**
         * get all inputs from request object
         *
         * @var array
         **/
        $inputs = $request->all();

        /**
         * get current route name to find white listed keys
         *
         * @var string
         **/
        $route = $request->route()->getName();

        /**
         * keys with route name that will ignored in striping the tags
         *
         * @var array
         **/
        $whitelistKeys = [
            'admin.feeds.store'                         => ['description'],
            'admin.feeds.update'                        => ['description'],
            'admin.recipe.store'                        => ['description'],
            'admin.recipe.update'                       => ['description'],
            'admin.masterclass.store'                   => ['description'],
            'admin.masterclass.update'                  => ['description'],
            'admin.masterclass.storeLession'            => ['description'],
            'admin.masterclass.updateLession'           => ['description'],
            'admin.appslides.store'                     => ['content', 'portal_content'],
            'admin.appslides.update'                    => ['content', 'portal_content'],
            'admin.marketplace.confirm-event-booking'   => ['description', 'notes', 'email_notes'],
            'admin.marketplace.create-event-slot'       => ['description', 'notes', 'email_notes'],
            'admin.bookings.edit-booked-event'          => ['description', 'notes', 'email_notes'],
            'admin.support.store'                       => ['description'],
            'admin.support.update'                      => ['description'],
            'admin.event.store'                         => ['description'],
            'admin.event.update'                        => ['description'],
            'admin.sessions.update'                     => ['notes'],
            'admin.clientlist.add-note'                 => ['note'],
            'admin.clientlist.edit-note'                => ['notes'],
            'admin.cronofy.clientlist.add-note'         => ['note'],
            'admin.cronofy.clientlist.edit-note'        => ['notes'],
            'admin.cronofy.sessions.update'             => ['notes'],
            'admin.cronofy.sessions.storeGroupSession'  => ['notes'],
            'admin.cronofy.sessions.updateGroupSession' => ['notes'],
            'admin.notifications.store'                 => ['message'],
            'admin.cronofy.consent-form.update'         => ['description', 'question_description'],
            'admin.contentChallenge.update'             => ['description'],
            'admin.cronofy.sessions.send-session-email' => ['email_message'],
            'admin.companies.storePortalFooterDetails'  => ['portal_footer_header_text'],
            'admin.companies.store'                     => ['contact_us_description'],
            'admin.companies.update'                    => ['contact_us_description'],
            'admin.companies.updateBanner'              => ['description'],
            'admin.companies.storeBanner'               => ['description'],
            'admin.admin-alerts.update'                 => ['description'],
            'admin.shorts.store'                        => ['description'],
            'admin.shorts.update'                       => ['description'],
        ];

        /**
         * HTMLPurifier Config array
         *
         * @var array
         **/
        $purifierConfig = HTMLPurifier_Config::createDefault();
        $purifierConfig->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $purifierConfig->set('CSS.AllowTricky', true);
        $purifierConfig->set('Cache.SerializerPath', '/tmp');

        // Allow iframes from: YouTube.com/Vimeo.com
        $purifierConfig->set('HTML.SafeIframe', true);
        $purifierConfig->set('URI.SafeIframeRegexp', '%^(http:|https:)?//(www.youtube(?:-nocookie)?.com/embed/|player.vimeo.com/video/)%');
        $purifierConfig->set('HTML.Trusted', true);
        $purifierConfig->set('Filter.YouTube', true);

        // Set some HTML5 properties
        $purifierConfig->set('HTML.DefinitionID', 'html5-definitions'); // unqiue id
        $purifierConfig->set('HTML.DefinitionRev', 2);

        // Allow css property
        $css_definition                   = $purifierConfig->getDefinition('CSS');
        $css_definition->info['left']     = new HTMLPurifier_AttrDef_Integer();
        $css_definition->info['right']    = new HTMLPurifier_AttrDef_Integer();
        $css_definition->info['tabindex'] = new HTMLPurifier_AttrDef_Integer();
        $css_definition->info['position'] = new HTMLPurifier_AttrDef_Enum(['static', 'absolute', 'fixed', 'relative', 'sticky', 'initial', 'inherit']);

        if ($def = $purifierConfig->maybeGetRawHTMLDefinition()) {
            // Content model actually excludes several tags, not modelled here
            $def->addElement('address', 'Block', 'Flow', 'Common');
            $def->addElement('hgroup', 'Block', 'Required: h1 | h2 | h3 | h4 | h5 | h6', 'Common');

            // http://developers.whatwg.org/grouping-content.html
            $def->addElement('figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common');
            $def->addElement('figcaption', 'Inline', 'Flow', 'Common');

            // http://developers.whatwg.org/the-video-element.html#the-video-element
            $def->addElement('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', [
                'src'      => 'URI',
                'type'     => 'Text',
                'width'    => 'Length',
                'height'   => 'Length',
                'poster'   => 'URI',
                'preload'  => 'Enum#auto,metadata,none',
                'controls' => 'Bool',
                'autoplay' => 'NMTOKENS',
                'loop'     => 'NMTOKENS',
            ]);
            $def->addElement('source', 'Block', 'Flow', 'Common', array(
                'src'  => 'URI',
                'type' => 'Text',
            ));

            $def->addElement('audio', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', [
                'src'          => 'URI',
                'autoplay'     => 'NMTOKENS',
                'loop'         => 'NMTOKENS',
                'controls'     => 'Bool',
                'controlslist' => 'NMTOKENS',
                'autoplay'     => 'NMTOKENS',
                'loop'         => 'NMTOKENS',
            ]);

            $def->addAttribute('div', 'data-oembed-url', 'URI');
            $def->addAttribute('div', 'data-responsive', 'Text');

            // http://developers.whatwg.org/text-level-semantics.html
            $def->addElement('s', 'Inline', 'Inline', 'Common');
            $def->addElement('var', 'Inline', 'Inline', 'Common');
            $def->addElement('sub', 'Inline', 'Inline', 'Common');
            $def->addElement('sup', 'Inline', 'Inline', 'Common');
            $def->addElement('mark', 'Inline', 'Inline', 'Common');
            $def->addElement('wbr', 'Inline', 'Empty', 'Core');

            // http://developers.whatwg.org/edits.html
            $def->addElement('ins', 'Block', 'Flow', 'Common', array('cite' => 'URI', 'datetime' => 'CDATA'));
            $def->addElement('del', 'Block', 'Flow', 'Common', array('cite' => 'URI', 'datetime' => 'CDATA'));

            // TinyMCE
            $def->addAttribute('img', 'data-mce-src', 'Text');
            $def->addAttribute('img', 'data-mce-json', 'Text');

            // Others
            $def->addAttribute('iframe', 'allow', 'NMTOKENS');
            $def->addAttribute('iframe', 'scrolling', 'Text');
            $def->addAttribute('iframe', 'src', 'Text');
            $def->addAttribute('iframe', 'allowfullscreen', 'Enum#allowfullscreen');
            $def->addAttribute('table', 'height', 'Text');
            $def->addAttribute('td', 'border', 'Text');
            $def->addAttribute('th', 'border', 'Text');
            $def->addAttribute('tr', 'width', 'Text');
            $def->addAttribute('tr', 'height', 'Text');
            $def->addAttribute('tr', 'border', 'Text');
            $def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
        }

        /**
         * HTMLPurifier Object
         *
         * @var HTMLPurifier
         **/
        $purifier = new HTMLPurifier($purifierConfig);

        // traverse array and sanitize each input
        array_walk_recursive($inputs, function (&$input, $key) use ($whitelistKeys, $route, $purifier) {
            if (isset($whitelistKeys[$route]) &&
                in_array($key, $whitelistKeys[$route])
            ) {
                // puritfy html
                $input = (!empty($input) ? preg_replace('#<script(.*?)>(.*?)</script>#is', '', $input) : $input);
                $input = (!empty($input) ? preg_replace('#<iframe(.*?)>(.*?)#is', '', $input) : $input);
                $input = (!empty($input) ? preg_replace('#<iframe(.*?)>(.*?)</iframe>#is', '', $input) : $input);
                $input = $purifier->purify($input);
            } else {
                // strip all tags
                $input = (!empty($input) ? strip_tags($input) : $input);
            }
            $input = (!empty($input) ? preg_replace('/\bon\w+=\S+(?=.*>)/', '', $input) : $input);
        });

        // merge sanitized inputs to actual request
        $request->merge($inputs);

        // continue request
        return $next($request);
    }
}
