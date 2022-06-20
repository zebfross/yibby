
export class <?= $upperName ?> {

    <?php foreach($props as $prop => $type): ?><?= $prop ?>: <?= $type['type'] ?> = <?= $type['default'] ?>;
    <?php endforeach; ?>

    static from(data){
        let ret = new <?= $upperName ?>()
        Object.assign(ret, data)
        return ret
    }

    constructor() {}
}
