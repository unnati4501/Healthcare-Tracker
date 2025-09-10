<script id="graphTemplate" type="text/html">
    <p class="m-0 font-19 text-center">{{ trans('labels.masterclass_feedback.mc_experience_score') }}</p>
    <div class="mb-4" style="margin-top: 85px;">
        <div class="progress experience-score-bar">#bar#</div>
        <ul class="experience-score-legend">#legend#</ul>
    </div>
</script>
<script id="graphBarTemplate" type="text/html">
    <div aria-valuemax="100" aria-valuemin="0" data-bs-html="true" aria-valuenow="#percentage#" class="progress-bar #feedbackClass#" data-placement="bottom" data-bs-toggle="tooltip" role="progressbar" style="width: #percentage#%" title="#tooltip#">
        #percentage#%
    </div>
</script>
<script id="graphLegendTemplate" type="text/html">
    <li class="#feedbackClass#">
        #feedbackName#
    </li>
</script>