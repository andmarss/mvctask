<!doctype html>
<html lang="en">
<?=import('includes/head');?>
<body>
    <?=import('includes/navbar');?>
    <div class="container">
        <?php if (\App\Auth::check() && (\App\Auth::id() === $user->id || \App\Auth::user()->admin)) : ?>

            <?php if (\App\Session::has('success')) : ?>
                <div class="alert alert-success" role="alert">
                    <?=\App\Session::flash('success')?>
                </div>
            <?php endif; ?>

            <?php if (isset($errors)) : ?>
                <div class="container">
                    <div class="alert alert-danger" role="alert">
                        <?php if(isset($errors) && is_string($errors)) : ?>
                            <p><?=$errors;?></p>
                        <?php endif; ?>

                        <?php if (isset($errors) && count($errors) > 0) : ?>
                            <?php if (!is_object($errors) && is_array($errors)) : ?>
                                <?php foreach ($errors as $error) : ?>
                                    <p><?=$error;?></p>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($user->name)) { ?>
                <h1>Привет, <?=$user->name?></h1>
            <?php } else { ?>
                <h1>Привет, пользователь!</h1>
            <?php } ?>

            <?php import('personal_area/edit', ['user' => $user]) ?>

        <?php else : ?>
            <h3>У вас нет прав для просмотра данной страницы.</h3>
        <?php endif; ?>
    </div>
    <script src="<?=asset('js/app.js')?>"></script>
</body>
</html>