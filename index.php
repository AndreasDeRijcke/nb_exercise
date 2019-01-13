<?php

/**
 * @file
 * NationBuilder API exercises.
 */

require 'vendor/autoload.php';
require 'src/NationBuilderClient.php';

use NationBuilderClient\NationBuilderClient;

if ($argc < 3) {
  exit("Usage: php index.php <slug> <token>\n");
}

// Start the exercise with given parameters.
$slug = $argv[1];
$token = $argv[2];

$exercise = new Exercise($slug, $token);
$exercise->run();

/**
 * Class Exercise.
 */
class Exercise {

  /**
   * @var string
   */
  private $slug;

  /**
   * @var string
   */
  private $token;

  public function __construct(string $slug, string $token) {
    $this->slug = $slug;
    $this->token = $token;
  }

  /**
   * Run the Exercise.
   *
   * The exercise steps:
   *
   * - Count people in Nation
   * - Create new person with first- and lastname.
   * - Updated created person with emaill address.
   * - Count people in Nation again. Amount should be one more as initial.
   * - Delete the created person.
   * - Count people in Nation again. Amount should be same as initial.
   */
  public function run() {

    static::showMessage("Connecting to NationBuilder with slug '{$this->slug}' and token: '{$this->token}'");

    $nb = new NationBuilderClient($this->slug, $this->token);

    // Get current amount of people in the nation.
    $response = $nb->request();

    if (isset($response['people_count'])) {
      static::showMessage("People count: Nation {$response['people_count']}");
    }
    else {
      static::abort($response, 'Oops, failed basic connection to NationBuilder.');
    }


    // Create new person.
    $time = time();
    $person = [
      'person' => [
        'first_name' => 'Test',
        'last_name' => "Person $time",
      ],
    ];

    $response = $nb->request('POST', 'people', json_encode($person));

    // We'll keep $new_person for later.
    $new_person = $response['person'] ?? [];
    if (!empty($new_person)) {
      static::showMessage("New person id: {$new_person['id']}");
      static::showMessage("New person name: '{$new_person['first_name']} {$new_person['last_name']}'");
    }
    else {
      static::abort($response, 'Seems we failed to create a new person.');
    }


    // Generate email address for this person and update this person.
    $email = [
      strtolower($new_person['first_name']),
      '.',
      strtolower($new_person['last_name']),
      '@andreasderijcke.be',
    ];
    $email = implode('', $email);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    $person = [
      'person' => [
        'email' => $email,
      ],
    ];

    $response = $nb->request('PUT', "people/{$new_person['id']}", json_encode($person));

    $person = $response['person'] ?? [];
    if (!empty($person)) {
      static::showMessage("The email address we added to '{$person['first_name']} {$person['last_name']}': {$person['email']}");
    }
    else {
      static::abort($response, 'Something went wrong updating this person.');
    }


    // Get current amount of people in the nation, should be 1 higher than before.
    $response = $nb->request();
    if (isset($response['people_count'])) {
      static::showMessage("People count: {$response['people_count']}");
    }


    // Delete the previously created person.
    $response = $nb->request('DELETE', "people/{$new_person['id']}");
    if (!isset($response['error'])) {
      static::showMessage("Person with id {$new_person['id']} deleted.");
    }


    // Get current amount of people in the nation again, should be one lower.
    $response = $nb->request();
    if (isset($response['people_count'])) {
      static::showMessage("People count in Nation: {$response['people_count']}");
    }

    static::showMessage("End of exercise.");
  }

  /*
   * Convenience functions
   */

  /**
   * Simple error handling function.
   *
   * @param array $response
   * @param string $message
   */
  private static function abort(array $response, string $message) {

    if (isset($response['error'])) {
      $message .= PHP_EOL . PHP_EOL;
      $message .= "{$response['error']}";
      $message .= PHP_EOL;
      $message .= "Exercise aborted";
    }

    exit($message);
  }

  /**
   * Wrapper around echo.
   *
   * @param string $message
   */
  private static function showMessage(string $message) {
    echo $message . PHP_EOL;
  }

}
