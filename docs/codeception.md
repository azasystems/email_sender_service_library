$ ./codecept.sh bootstrap                                                          
Bootstrapping Codeception

- File codeception.yml created       <- global configuration                                                                                   
- UnitTester actor has been created in tests/Support                                                                                          
- Actions have been loaded                                                                                                                    
- tests/Unit created                 <- unit tests                                                                                             
- tests/Unit.suite.yml written       <- unit tests suite configuration                                                                         
- FunctionalTester actor has been created in tests/Support                                                                                    
- Actions have been loaded                                                                                                                    
- tests/Functional created           <- 'functional tests'                                                                                       
- tests/Functional.suite.yml written <- 'functional tests' suite configuration                                                                   
- AcceptanceTester actor has been created in tests/Support                                                                                    
- Actions have been loaded                                                                                                                    
- tests/Acceptance created           <- acceptance tests                                                                                       
- tests/Acceptance.suite.yml written <- acceptance tests suite configuration
 ---                                                                                                                                         

- Codeception is installed for acceptance, functional, and unit testing

- Next steps:
- 1. Edit tests/acceptance.suite.yml to set url of your application. Change PhpBrowser to WebDriver to enable browser testing
- 2. Edit tests/functional.suite.yml to enable a framework module. Remove this file if you don't use a framework
- 3. Create your first acceptance tests using codecept g:cest acceptance First
- 4. Write first test in tests/acceptance/FirstCest.php
- 5. Run tests using: codecept run
