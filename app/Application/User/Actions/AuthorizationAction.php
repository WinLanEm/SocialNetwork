<?php

namespace App\Application\User\Actions;



use App\Application\User\DTOs\AuthorizationDTO;
use App\Domain\User\Actions\AuthorizationActionInterface;
use App\Domain\User\Repositories\AuthUserRepositoryInterface;
use App\Domain\User\Tasks\PreparePhoneTaskInterface;

class AuthorizationAction implements AuthorizationActionInterface
{
    private AuthUserRepositoryInterface $authUserRepository;
    private PreparePhoneTaskInterface $phoneTask;
    public function __construct(AuthUserRepositoryInterface $authUserRepository,
                                PreparePhoneTaskInterface $phoneTask)
    {
        $this->authUserRepository = $authUserRepository;
        $this->phoneTask = $phoneTask;
    }

    public function exec(AuthorizationDTO $DTO): bool
    {
        $preparedPhone = $this->phoneTask->exec($DTO->phone);
        return $this->authUserRepository->exec($preparedPhone,$DTO->password);
    }
}
