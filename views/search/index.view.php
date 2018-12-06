<form class="form-inline md-form form-sm search-form clearfix" method="GET" action="{{route('results')}}">
    <div class="form-row">
        <div class="form-group col-md-6 col-xs-12">
            <label class="search-form-label" for="inputState">Показывать</label>
            <select id="inputState" name="completed" class="form-control">
                <option value="1">Все</option>
                <option value="2">Открытые</option>
                <option value="3">Завершенные</option>
            </select>
        </div>
        <div class="form-group col-md-6 col-xs-12 text-right">
            <input class="form-control form-control-sm mr-3 w-35" type="text" name="query" placeholder="Найти по имени пользователя или email-адресу" aria-label="Search">
            <i class="fa fa-search" aria-hidden="true"></i>
            <button class="btn btn-primary mb-2">Найти</button>
        </div>
    </div>
</form>