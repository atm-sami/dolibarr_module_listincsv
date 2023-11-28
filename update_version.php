<?php

// Récupérer le nom de la branche de la PR
$branchName = getenv('GITHUB_HEAD_REF'); // GitHub env variable for the source branch of the PR
$branchParts = explode('_', $branchName);
$changeType = strtoupper($branchParts[0]); // NEW ou FIX
$featureName = $branchParts[1]; // Nom de la fonctionnalité

// Rechercher le fichier mod*.php dans core/modules/
$moduleFile = current(glob('core/modules/mod*.php'));

if ($moduleFile) {
    // Lire le fichier et mettre à jour la version
    $content = file_get_contents($moduleFile);
    preg_match('/\$this->version\s*=\s*["\']([^"\']+)["\'];/', $content, $matches);
    if (isset($matches[1])) {
        $currentVersion = $matches[1];
        $newVersion = incrementVersion($currentVersion);
        $content = preg_replace('/(\$this->version\s*=\s*["\'])([^"\']+)["\'];/', '$1' . $newVersion . '";', $content);
        file_put_contents($moduleFile, $content);
        echo "Version mise à jour dans $moduleFile\n";
    }
} else {
    echo "Aucun fichier correspondant trouvé dans core/modules/\n";
}

// Ajouter une ligne au fichier ChangeLog.md
$changelogFile = 'ChangeLog.md';
$changelogEntry = "* [$changeType] $featureName";
file_put_contents($changelogFile, $changelogEntry . PHP_EOL, FILE_APPEND);
echo "Changelog mis à jour dans $changelogFile\n";

function incrementVersion($currentVersion) {
    list($major, $minor, $patch) = explode('.', $currentVersion);
    $major++;
    return "$major.$minor.$patch";
}
