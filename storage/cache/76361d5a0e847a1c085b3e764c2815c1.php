<div class="panel panel-default">
    <div class="panel-heading">Создайте новую задачу</div>

    <div class="panel-body">
        <form enctype="multipart/form-data" action="<?=route('store');?>" method="post" id="store-form">
            <?=csrf_field();?>

            <div class="form-group">
                <label for="title">Заголовок задачи</label>
                <input value="<?=old('title');?>" name="title" class="form-control title" required>
            </div>

            <div class="form-group">
                <label for="picture" class="file-label personalArea__file-label--required"><i class="fas fa-download"></i>Выберите файл</label>
                <span id="remove-image" class="remove-image"><i class="fas fa-times"></i></span>
                <input type="file" name="picture" id="picture" class="hidden" required>
                <div id="preview" class="preview">
                    <img src="" alt="">
                </div>
            </div>
            <div class="form-group">
                <label for="content">Описание задачи</label>
                <textarea name="content" id="content" cols="30" rows="7" class="form-control" required><?=old('content');?></textarea>
            </div>

            <div class="form-group">
                <div class="text-right">
                    <span class="btn btn-default preview-btn">Предварительный просмотр</span><button class="btn btn-success">Добавить задачу</button>
                </div>
            </div>
        </form>
    </div>

    <div class="panel-body" id="clone">
        <span class="close-clone"><i class="fas fa-times"></i></span>
        <div class="panel-heading">
            <span><?=isset(\App\Auth::user()->name) ? \App\Auth::user()->name : 'Пользователь';?>, <b><?=(new DateTime())->format('d-m-Y');?></b></span>
            <span class="btn pull-right btn-danger btn-xs">Открыта</span>
        </div>
        <div class="panel-body">
            <div class="text-center">
                <img src="" class="rounded clone-image">
            </div>
            <h4 class="text-center clone-title"></h4>
            <p class="text-center clone-content"></p>
        </div>
    </div>
</div>