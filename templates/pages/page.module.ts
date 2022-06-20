import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { <?= $upperName ?>PageRoutingModule } from './<?= $name ?>-routing.module';

import { <?= $upperName ?>Page } from './<?= $name ?>.page';

@NgModule({
  imports: [
    CommonModule,
    ReactiveFormsModule,
    FormsModule,
    IonicModule,
    <?= $upperName ?>PageRoutingModule
  ],
  declarations: [<?= $upperName ?>Page]
})
export class <?= $upperName ?>PageModule {}
