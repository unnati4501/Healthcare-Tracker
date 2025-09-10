<div class="col-sm-12">
    <div class="form-group">
        {{ Form::textarea('notes', old('notes', (!empty($notes) ? htmlspecialchars_decode($notes) : null)), ['id' => 'notes', 'cols' => 10, 'class' => 'form-control h-auto basic-format-ckeditor', 'placeholder' => 'Enter notes', 'data-errplaceholder' => '#notes-error', 'data-formid' => '#sessionEdit']) }}
        <div id="notes-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #dc3545;">
        </div>
    </div>
</div>