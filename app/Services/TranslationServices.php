<?php

namespace App\Services;

use App\Models\Translation;

use App\Repositories\TranslationRepository;

class TranslationServices
{
    protected TranslationRepository $repository;

    public function __construct(TranslationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): Translation
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Translation
    {
        return $this->repository->update($id, $data);
    }

    public function export() 
    {   
        return $this->repository->export();
    }
}   