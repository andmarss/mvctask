
<?php \App\Section::start('content'); ?>
    <div class="container">
        <?php if(\App\Session::has('success')): ?>
            <div class="alert alert-success" role="alert">
                <?=\App\Session::flash('success');?>
            </div>
        <?php endif; ?>

        <?php if(\App\Session::has('error')): ?>

            <?php if($errors = \App\Session::flash('error')): ?>

                <div class="container">
                    <div class="alert alert-danger" role="alert">
                        <?php if(isset($errors) && is_string($errors)): ?>
                            <p><?=$errors;?></p>
                        <?php endif; ?>

                        <?php if(isset($errors) && count($errors) > 0): ?>

                            <?php if(!is_object($errors) && is_array($errors)): ?>

                                <?php foreach ($errors as $error): ?>

                                    <?php if(is_array($error)): ?>

                                        <?php foreach ($error as $err): ?>

                                            <p><?=$err;?></p>

                                        <?php endforeach; ?>

                                    <?php elseif(is_string($error)): ?>

                                        <p><?=$error;?></p>

                                    <?php endif; ?>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        <?php endif; ?>
                    </div>
                </div>

            <?php endif; ?>

        <?php endif; ?>

        <?php if(\App\Auth::check()): ?>
            <?=view('add_task_form/index')->with(get_defined_vars())->render(); ?>
        <?php endif; ?>

        <?php if(count($tasks) > 0): ?>
            <?=view('search/index')->with(get_defined_vars())->render(); ?>
        <?php endif; ?>

        <?php if(count($tasks) > 0): ?>
            <ul class="list-group clearfix">
                <?php foreach($tasks as $task): ?>
                    <?=view('includes/task', ['task' => $task])->with(get_defined_vars())->render(); ?>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <h3>Задач еще нет.
                <?php if(!\App\Auth::check()): ?>
                    <br><small class="text-muted"><a href="<?=route('register-index');?>">Зарегестрируйтесь</a> или <a href="<?=route('login-index');?>">авторизируйтесь</a>, что бы добавить свою задачу.</small>
                <?php endif; ?>
            </h3>
        <?php endif; ?>

        <?php if(count($tasks) > 0): ?>
            <?=paginate($per_page, $total);?>
        <?php endif; ?>
    </div>
<?php \App\Section::stop(); ?>
<?=view('layer/main')->with(get_defined_vars())->render(); ?>