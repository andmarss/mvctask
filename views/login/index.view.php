<!doctype html>
<html lang="en">
<?=import('includes/head');?>
<body>
<?=import('includes/navbar');?>

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

    <div class="container">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Авторизация</div>
                <div class="panel-body">
                    <form role="form" method="POST" enctype="multipart/form-data" action="<?=route('login')?>" class="form-horizontal">
                        <input type="hidden" name="token" value="<?=csrf_token();?>">

                        <div class="form-group">
                            <label for="email" class="col-md-4 control-label">Email адрес</label>
                            <div class="col-md-6">
                                <input id="email" type="email" name="email" value="<?=old('email')?>" required="required" autofocus="autofocus" class="form-control">
                                <?php if (isset($errors) && count($errors) > 0 && isset($errors->email)) : ?>
                                    <?php foreach ($errors->email as $error) : ?>
                                        <p class="text-danger"><?=$error;?></p>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password" class="col-md-4 control-label">Пароль</label>
                            <div class="col-md-6">
                                <input id="password" type="password" name="password" required="required" class="form-control" value="<?=old('password')?>">
                                <?php if (isset($errors) && count($errors) > 0 && isset($errors->password)) : ?>
                                    <?php foreach ($errors->password as $error) : ?>
                                        <p class="text-danger"><?=$error;?></p>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remember"> Запомнить
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Войти
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script src="<?=asset('js/app.js')?>"></script>
</body>
</html>