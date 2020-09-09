<?php


namespace RouterOS\Generator\Provider\Ansible\Model;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use RouterOS\Generator\Provider\Ansible\Contracts\ModuleManagerInterface;

class ModuleManager implements ModuleManagerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var int
     */
    private $maxResults;

    public function __construct(
        EntityManagerInterface $em,
        $maxResults = 1000
    )
    {
        $this->em = $em;
        $this->maxResults = $maxResults;
    }

    public function create(): Module
    {
        return new Module();
    }


    public function findOrCreate(string $name): Module
    {
        $object = $this->findByName($name);

        if(is_null($object)){
            $object = $this->create();
            $object->setName($name);
        }

        return $object;
    }


    public function findByName(string $name)
    {
        $repository = $this->getRepository();

        return $repository->findOneBy(['name' => $name]);
    }

    public function getModuleList(): array
    {
        $em = $this->em;
        $maxResults = $this->maxResults;
        $builder = $em->createQueryBuilder();

        $builder
            ->select('a.id, a.name')
            ->from(Module::class, 'a')
        ;

        $builder->setMaxResults($maxResults);
        return $builder->getQuery()->getArrayResult();
    }

    public function getRepository(): ObjectRepository
    {
        return $this->em->getRepository(Module::class);
    }

    public function update(Module $module, $andFlush = true): void
    {
        $em = $this->em;

        $em->persist($module);

        if($andFlush){
            $em->flush();
        }
    }

}