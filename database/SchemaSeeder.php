<?php

namespace Database;

interface SchemaSeeder {
    public function seed(): void;
    public function createRowData(): array;
}
