{{ Form::hidden("questions[{$id}]", "{$valquestion_id}", ["id" => "questions_{$id}"]) }}
{{ Form::hidden("category[{$id}]", "{$valcategory}", ["id" => "category_{$id}"]) }}
{{ Form::hidden("subcategory[{$id}]", "{$valsubcategory}", ["id" => "subcategory_{$id}"]) }}
{{ Form::hidden("questions_type[{$id}]", "{$valquestions_type}", ["id" => "questions_type_{$id}"]) }}
