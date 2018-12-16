<div class="col-8">
    <h1>
        <?php echo($petition->title); ?>
    </h1>
    <p class="text-justify">
        <?php echo($petition->description); ?>
    </p>
    <div>
        Автор: <?php echo($petition->author); ?>
    </div>
    <div>
        Подписей: <?php echo($petition->countOfVotes); ?>
    </div>
</div>

<div class="col-4">
    <div class="card">
        <div class="card-body">

            <h5 class="card-title">Подписать петицию</h5>

            <form action="/petitions/show/<?php echo($petition->id); ?>"
                  method="POST">
                <div class="form-group">
                    <input name="subsEmail" type="email" class="form-control"
                           placeholder="Введите email">
                </div>

                <input type="hidden" name="subsPetitionId"
                       value="<?php echo($petition->id); ?>">

                <button name="btnSubmit" type="submit"
                        class="btn btn-primary">
                    Подписать</button>
            </form>

            <?php echo $messageStatus; ?>

        </div>
    </div>
</div>