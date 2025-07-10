describe('Authentification: Inscription et Connexion', () => {
  const testEmail = `user${Date.now()}@test.com`;
  const testPassword = 'Test1234!';
  const testPseudo = `TestUser${Date.now()}`;
  const roomName = 'Salle de test';
  const messageContent = 'Ceci est un message de test';
  beforeEach(() => {
    cy.visit('http://localhost:5173/');
  });

  it('ouvre le modal d’authentification', () => {
    cy.contains('Connexion').click(); // Bouton du header
    cy.get('.loginapp').should('exist');
  });

  it('remplit le formulaire d’inscription et s’inscrit', () => {
    cy.contains('Connexion').click();
    cy.contains('Inscription').click();

    cy.get('[data-testid="register-pseudo"]').eq(0).type(testPseudo); // Pseudo
    cy.get('input[type="email"]').type(testEmail); // Email
    cy.get('input[type="password"]').eq(0).type(testPassword); // Password
    cy.get('input[type="password"]').eq(1).type(testPassword); // Confirm password

    cy.get('form').submit();

    cy.contains('Inscription réussie !', { timeout: 20000 }).should('exist');
  });

  it('se connecte et créer une room', () => {
    cy.contains('Connexion').click();

    cy.get('input[type="email"]').type(testEmail);
    cy.get('input[type="password"]').type(testPassword);

    cy.get('form').submit();

    cy.contains('Connexion réussie !', { timeout: 20000 }).should('exist');

    // Vérifie que le cookie JWT est bien présent
    cy.getCookie('jwt').should('exist');

    cy.wait(4000);
    cy.reload();

    cy.get('img[alt="Echec"]').click();

    cy.get('input[placeholder="Nom de la salle"]').type(roomName);

    cy.get('button[type="submit"]').click();

    cy.contains(`${roomName} (0/2)`).should('exist');

    cy.contains(`${roomName} (0/2)`)
      .parent()
      .within(() => {
        cy.contains('Supprimer').click();
      });

    // 6. Vérifie qu’elle a été supprimée
    cy.contains(`${roomName} (0/2)`).should('not.exist');
    });

    it('se connecte et envoye un message', () => {
        cy.contains('Connexion').click();

        cy.get('input[type="email"]').type(testEmail);
        cy.get('input[type="password"]').type(testPassword);

        cy.get('form').submit();

        cy.contains('Connexion réussie !', { timeout: 20000 }).should('exist');

        // Vérifie que le cookie JWT est bien présent
        cy.getCookie('jwt').should('exist');

        cy.wait(4000);
        cy.reload();

        cy.get('[data-testid="all-messages-button"]').click();

        cy.get('input[placeholder="Écrire un message..."]').type(messageContent);

        cy.get('[data-testid="button-envoyer"]').click();

        cy.get('.bg-blue-100').should('contain.text', messageContent);
    });

  });

  

