import { Component, OnInit } from '@angular/core';
import { FormArray, FormBuilder, FormGroup } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';

@Component({
  selector: 'app-<?= $name ?>',
  templateUrl: './<?= $name ?>.page.html',
  styleUrls: ['./<?= $name ?>.page.scss'],
})
export class <?= $upperName ?>Page implements OnInit {
  form: FormGroup;
  model;

  constructor(private formBuilder: FormBuilder, private route: ActivatedRoute) {
    let id = this.route.snapshot.queryParams['id'];
    this.model = null;
    this.form = this.formBuilder.group(this.model);

    <?php foreach($groupFields as $field): ?>
    this.form.setControl(
        '<?= $field['id'] ?>',
        this.formBuilder.array(this.model.<?= $field['id'] ?>.map((item) => this.formBuilder.group(item)))
    );
    <?php endforeach; ?>
  }

  ngOnInit() {}

  onSubmit() {
    if (this.form.valid) {
      this.form.reset();
      // this.service.save(this.form.value);
    }
  }

  <?php foreach($groupFields as $field): ?>

  get <?= $field['id'] ?>Controls() {
    return this.form.controls['<?= $field['id'] ?>'] as FormArray;
  }

  add<?= ucwords($field['id']) ?>() {
    this.<?= $field['id'] ?>Controls.push(this.formBuilder.group(new <?= ucwords($field['id']) ?>()));
  }

  delete<?= ucwords($field['id']) ?>(index: number) {
    this.<?= $field['id'] ?>Controls.removeAt(index);
  }

  <?php endforeach; ?>

}
