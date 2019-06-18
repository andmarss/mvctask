<div class="panel panel-default">
    <div class="panel-heading">Изменить данные пользователя</div>

    <div class="panel-body">
        <form enctype="multipart/form-data" action="<?=route('user.edit', ['id' => $user->id]);?>" method="post" id="store-form">
            <?=csrf_field();?>

            <div class="form-group">
                <label for="title">Имя</label>
                <input value="<?=isset($user->name) ? $user->name : '';?>" name="name" class="form-control title" required>

                <?php if (isset($errors) && isset($errors->name)): ?> : ?>
                    <?php foreach ($errors->name as $error): ?>
                        <p class="text-danger"><?=$error;?></p>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="title">Email-адрес</label>
                <input value="<?=isset($user->email) ? $user->email : '';?>" name="email" class="form-control title" required>

                <?php if (isset($errors) && isset($errors->email)): ?>
                    <?php foreach ($errors->email as $error): ?>
                        <p class="text-danger"><?=$error;?></p>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="title">Пароль</label>
                <input value="" name="password" type="password" class="form-control title">
            </div>

            <div class="form-group">
                <label for="title">Номер телефона</label>
                <input value="<?=isset($user->phone) ? $user->phone : '';?>" name="phone" class="form-control title" required>

                <?php if (isset($errors) && isset($errors->phone)): ?>
                    <?php foreach ($errors->phone as $error): ?>
                        <p class="text-danger"><?=$error;?></p>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <div class="text-right">
                    <button class="btn btn-success">Изменить данные</button>
                </div>
            </div>
        </form>
    </div>
</div>