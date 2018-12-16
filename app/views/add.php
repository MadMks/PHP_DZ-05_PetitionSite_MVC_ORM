<div class="col-6">
    <form action="/petitions/add" method="POST">

        <div class="form-group">
            <input type="text" class="form-control" name="title" placeholder="Название">
        </div>

        <div class="form-group">
            <input type="email" name="email" class="form-control" placeholder="Email">
        </div>
        <div class="form-group">
                <textarea name="description" class="form-control"
                          placeholder="Описание"></textarea>
        </div>

        <button class="btn btn-primary" type="submit"
                name="btnSubmit" value="Add">Добавить</button>

    </form>
</div>

<div class="col-6">

    <?php echo $messageStatus; ?>

</div>