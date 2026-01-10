<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Category;
use App\Entity\Publisher;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Users
        $admin = new User();
        $admin->setEmail('admin@bookstore.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'password'));
        $admin->setFirstname('Admin');
        $admin->setLastname('System');
        $manager->persist($admin);

        $agent = new User();
        $agent->setEmail('agent@bookstore.com');
        $agent->setRoles(['ROLE_AGENT']);
        $agent->setPassword($this->hasher->hashPassword($agent, 'password'));
        $agent->setFirstname('Agent');
        $agent->setLastname('Smith');
        $manager->persist($agent);

        $abonne = new User();
        $abonne->setEmail('abonne@bookstore.com');
        $abonne->setRoles(['ROLE_ABONNE']);
        $abonne->setPassword($this->hasher->hashPassword($abonne, 'password'));
        $abonne->setFirstname('John');
        $abonne->setLastname('Doe');
        $abonne->setAddresses(['12 rue de la Paix, Paris']);
        $manager->persist($abonne);

        // Categories
        $categories = [];
        for ($i = 0; $i < 5; $i++) {
            $category = new Category();
            $category->setName($faker->word);
            $manager->persist($category);
            $categories[] = $category;
        }

        // Publishers
        $publishers = [];
        for ($i = 0; $i < 5; $i++) {
            $publisher = new Publisher();
            $publisher->setName($faker->company);
            $manager->persist($publisher);
            $publishers[] = $publisher;
        }

        // Authors
        $authors = [];
        for ($i = 0; $i < 10; $i++) {
            $author = new Author();
            $author->setName($faker->name);
            $manager->persist($author);
            $authors[] = $author;
        }

        // Books
        for ($i = 0; $i < 30; $i++) {
            $book = new Book();
            $book->setTitle($faker->sentence(3));
            $book->setIsbn($faker->isbn13);
            $book->setDescription($faker->paragraph);
            $book->setPrice($faker->randomFloat(2, 10, 50));
            $book->setStock($faker->numberBetween(0, 100));
            
            $book->setCategory($faker->randomElement($categories));
            $book->setPublisher($faker->randomElement($publishers));
            $book->addAuthor($faker->randomElement($authors));
            
            // Set coverImage string directly (simulating uploaded file)
            // Ensure you have a placeholder.jpg in public/uploads/covers/
            //$book->setCoverImage('placeholder.jpg'); 

            $manager->persist($book);
        }

        $manager->flush();
    }
}
