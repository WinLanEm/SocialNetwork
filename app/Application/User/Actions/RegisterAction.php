<?php

namespace App\Application\User\Actions;


use App\Application\User\DTOs\RegisterDTO;
use App\Domain\User\Actions\RegisterActionInterface;
use App\Domain\User\Repositories\CreateUserRepositoryInterface;
use App\Domain\User\Tasks\PreparePhoneTaskInterface;
use App\Infrastructure\User\Repositories\CreateUserRepository;

class RegisterAction implements RegisterActionInterface
{
    private PreparePhoneTaskInterface $phoneTask;
    private CreateUserRepository $createUserRepository;
    public function __construct(PreparePhoneTaskInterface $phoneTask,CreateUserRepositoryInterface $createUserRepository)
    {
        $this->phoneTask = $phoneTask;
        $this->createUserRepository = $createUserRepository;
    }

    public function exec(RegisterDTO $DTO): bool
    {
        $preparedPhone = $this->phoneTask->exec($DTO->phone);
        if(!$preparedPhone || $DTO->password !== $DTO->password_confirmation){
            return false;
        }else{
            return (bool)$this->createUserRepository->exec($DTO);
        }
    }
}
