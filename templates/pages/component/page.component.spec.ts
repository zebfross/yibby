import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';
import { IonicModule } from '@ionic/angular';

import { <?= $upperName ?>Component } from './<?= $name ?>.component';

describe('<?= $upperName ?>Component', () => {
  let component: <?= $upperName ?>Component;
  let fixture: ComponentFixture<<?= $upperName ?>Component>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [ <?= $upperName ?>Component ],
      imports: [IonicModule.forRoot()]
    }).compileComponents();

    fixture = TestBed.createComponent(<?= $upperName ?>Component);
    component = fixture.componentInstance;
    fixture.detectChanges();
  }));

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
