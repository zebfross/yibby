import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';

@Component({
  selector: 'app-<?= $name ?>',
  templateUrl: './<?= $name ?>.page.html',
  styleUrls: ['./<?= $name ?>.page.scss'],
})
export class <?= $upperName ?>Page implements OnInit {
  form: FormGroup;

  constructor(private formBuilder: FormBuilder, private route: ActivatedRoute) {
    let id = this.route.snapshot.queryParams['id'];
    let model = null;
    this.form = this.formBuilder.group(model);
  }

  ngOnInit() {}

  onSubmit() {
    if (this.form.valid) {
      this.form.reset();
      // this.service.save(this.form.value);
    }
  }

}
