<?php

namespace PHPCR\Shell\Config;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use PHPCR\Shell\Config\Exception\FileExistsException;
use PHPCR\Shell\Console\Helper\ConfigHelper;

class ProfileLoader
{
    const DIR_PROFILE = 'profiles';

    protected $config;
    protected $filesystem;

    public function __construct(ConfigHelper $config, Filesystem $filesystem = null)
    {
        $this->config = $config;
        $this->filesystem = $filesystem ? : new Filesystem;
    }

    protected function getProfileDir()
    {
        return sprintf('%s/%s', $this->config->getConfigDir(), self::DIR_PROFILE);
    }

    public function getProfilePath($name)
    {
        return sprintf('%s/%s/%s.yml', $this->config->getConfigDir(), self::DIR_PROFILE, $name);
    }

    public function getProfileNames()
    {
        $dir = $this->getProfileDir();

        if (false === $this->filesystem->exists($dir)) {
            return array();
        }

        $files = Finder::create()->files()->name('*.yml')->in($dir);

        $profiles = array();
        foreach ($files as $file) {
            $profiles[] = substr($file->getBasename(), 0, -4);
        }

        sort($profiles);

        return $profiles;
    }

    public function loadProfile(Profile $profile)
    {
        $path = $this->getProfilePath($profile->getName());

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('Profile "%s" does not exist, expected to find it in "%s"',
                $profile->getName(), $path
            ));
        }

        $contents = file_get_contents($path);
        $data = Yaml::parse($contents);

        if (isset($data['transport'])) {
            $profile->set('transport', $data['transport']);
        }

        if (isset($data['phpcr'])) {
            $profile->set('phpcr', $data['phpcr']);
        }
    }

    public function saveProfile(Profile $profile, $overwrite = false)
    {
        $profileDir = $this->getProfileDir();
        $path = $this->getProfilePath($profile->getName());

        if (false === $overwrite && file_exists($path)) {
            throw new FileExistsException(sprintf(
                'Profile already exists at "%s"', $path
            ));
        }

        $yaml = Yaml::dump($profile->toArray());

        $this->filesystem->dumpFile($path, $yaml, 0600);
    }
}
