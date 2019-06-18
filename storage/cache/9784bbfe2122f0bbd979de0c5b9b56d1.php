
<?php \App\Section::start('content'); ?>

    <div class="container">
        <?php if (\App\Session::has('success')): ?>
            <div class="alert alert-success" role="alert">
                <?=\App\Session::flash('success');?>
            </div>
        <?php endif; ?>

        <?php if (\App\Session::has('error')): ?>
            <div class="alert alert-danger" role="alert">
                <?=\App\Session::flash('error');?>
            </div>
        <?php endif; ?>

        <?php if(isset($query)): ?>
            <h3><?=$query;?></h3>
        <?php endif; ?>

        <?php if (count($tasks) > 0): ?><?php foreach ($tasks as $task): ?>
            <?=view('includes/task', ['task' => $task])->with(get_defined_vars())->render(); ?>;
        <?php endforeach; ?><?php else: ?>
            <h4>Результатов не найдено</h4>
        <?php endif; ?>
    </div>

<?php \App\Section::stop(); ?>
<?=view('layer/main')->with(get_defined_vars())->render(); ?>