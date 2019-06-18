
<?php \App\Section::start('content'); ?>
    <div class="container">
        <?php if (\App\Auth::check() && (\App\Auth::id() === $user->id || \App\Auth::user()->admin)): ?>

            <?php if (\App\Session::has('success')): ?>
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

            <?php if (isset($user->name)): ?>
                <h1>Привет, <?=$user->name;?></h1>
            <?php else: ?>
                <h1>Привет, пользователь!</h1>
            <?php endif; ?>

            <?=view('personal_area/edit', ['user' => $user])->with(get_defined_vars())->render(); ?>

            <?php if(count($tasks) > 0): ?>
                <h3>Список ваших задач</h3>

                <?=view('personal_area/table', ['tasks' => $tasks])->with(get_defined_vars())->render(); ?>
            <?php endif; ?>

        <?php else: ?>
            <h3>У вас нет прав для просмотра данной страницы.</h3>
        <?php endif; ?>
    </div>
<?php \App\Section::stop(); ?>
<?=view('layer/main')->with(get_defined_vars())->render(); ?>