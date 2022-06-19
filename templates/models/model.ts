
export class <?= $upperName ?> {

    <?php foreach($props as $prop => $type): ?><?= $prop ?>: <?= $type['type'] ?>;
    <?php endforeach; ?>

    static from(data){
        let ret = new <?= $upperName ?>()
        Object.assign(ret, data)
        return ret
    }

    constructor() {

    <?php foreach($props as $prop => $type): ?>
    this.<?= $prop ?> = <?= $type['default'] ?>;
    <?php endforeach; ?>
}
}
