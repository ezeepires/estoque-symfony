<?php

namespace App\Command;

use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Cria um usuário com ROLE_USER no banco de dados',
)]
class CreateUserCommand
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument('E-mail do novo usuário')] string $email,
        #[Option('Senha do novo usuário (se omitida, será solicitada)')] ?string $password = null,
    ): int {
        $email = mb_strtolower(trim($email));

        if (!filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            $io->error(sprintf('E-mail inválido: "%s".', $email));

            return Command::INVALID;
        }

        if (null === $password) {
            if (!$io->isInteractive()) {
                $io->error('Em modo não interativo, informe a senha com --password.');

                return Command::INVALID;
            }

            $password = $io->askHidden('Senha (mínimo 8 caracteres):');
        }

        if (null === $password || \strlen($password) < 8) {
            $io->error('A senha deve ter pelo menos 8 caracteres.');

            return Command::INVALID;
        }

        if (null !== $this->entityManager->getRepository(Usuario::class)->findOneBy(['email' => $email])) {
            $io->error(sprintf('Já existe um usuário com o e-mail "%s".', $email));

            return Command::FAILURE;
        }

        $usuario = new Usuario();
        $usuario->setEmail($email);
        // getRoles() já adiciona ROLE_USER automaticamente, então não é preciso gravá-la.
        $usuario->setRoles([]);
        $usuario->setPassword($this->passwordHasher->hashPassword($usuario, $password));

        $this->entityManager->persist($usuario);
        $this->entityManager->flush();

        $io->success(sprintf('Usuário "%s" criado com sucesso (ROLE_USER).', $email));

        return Command::SUCCESS;
    }
}
