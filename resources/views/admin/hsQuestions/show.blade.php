<table class="table table-bordered table-hover m-0">
    <thead>
        <tr>
            <th class="text-center" scope="col">
                Sr no
            </th>
            <th class="text-center" scope="col">
                Options
            </th>
            <th class="text-center" scope="col">
                Weightage
            </th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 1 ?>
        @foreach($hsQuestions->hsQuestionsOptions as $questionOptionData)
        <tr>
            <th class="text-center">
                {{$i++}}
            </th>
            <td class="text-center">
                {{$questionOptionData->choice}}
            </td>
            <td class="text-center">
                {{$questionOptionData->score}}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
