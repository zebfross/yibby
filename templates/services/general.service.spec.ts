import { TestBed } from '@angular/core/testing';

import { <?= $upperName ?>Service } from './<?= $name ?>.service';

describe('<?= $upperName ?>Service', () => {
    let service: <?= $upperName ?>Service;

    beforeEach(() => {
        TestBed.configureTestingModule({});
        service = TestBed.inject(<?= $upperName ?>Service);
    });

    it('should be created', () => {
        expect(service).toBeTruthy();
    });
});