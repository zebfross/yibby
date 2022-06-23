import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';
import { IonicModule } from '@ionic/angular';

import { <?= $upperName ?>Page } from './<?= $name ?>.page';

describe('<?= $upperName ?>Page', () => {
  let component: <?= $upperName ?>Page;
  let fixture: ComponentFixture<<?= $upperName ?>Page>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [ <?= $upperName ?>Page ],
      imports: [IonicModule.forRoot()]
    }).compileComponents();

    fixture = TestBed.createComponent(<?= $upperName ?>Page);
    component = fixture.componentInstance;
    fixture.detectChanges();
  }));

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
