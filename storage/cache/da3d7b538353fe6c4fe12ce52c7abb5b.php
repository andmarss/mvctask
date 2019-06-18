<?php if(isset($tasks)): ?>
    <div class="container h-300">
        <table class="table table-striped">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Заголовок</th>
                <th scope="col">Статус</th>
                <th scope="col">Дата</th>
            </tr>
            </thead>
            <tbody>
                <?php foreach($tasks as $idx => $task): ?>
                    <tr>
                        <th scope="row"><?=$idx+1;?></th>
                        <td><?=$task->title;?></td>
                        <td><?=$task->completed ? 'Закрыта' : 'Открыта';?></td>
                        <td><?=$task->created_at;?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>