# O2TI Change Order Shipping Method

## Descrição

O módulo **O2TI Change Order Shipping Method** adiciona a capacidade de alterar o método de envio de pedidos já existentes no Magento 2.

Este módulo permite que administradores autorizados modifiquem o método de envio de um pedido após ele ter sido criado, com as seguintes funcionalidades:

- Alteração do método de envio em pedidos existentes
- Registro automático das alterações no histórico do pedido
- Configurações de controle de acesso por status do pedido
- Notificação opcional ao cliente sobre alterações
- Registro do administrador que realizou a alteração

## Requisitos

- Magento 2.3.x ou superior
- PHP 7.3 ou superior

## Instalação

### Via Composer

```bash
composer require o2ti/module-change-order-shipping-method
bin/magento module:enable O2TI_ChangeOrderShippingMethod
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
```

### Instalação Manual

1. Crie a estrutura de pastas `app/code/O2TI/ChangeOrderShippingMethod` no seu projeto Magento
2. Extraia os arquivos do módulo para o diretório criado
3. Execute os seguintes comandos:

```bash
bin/magento module:enable O2TI_ChangeOrderShippingMethod
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
```

## Configuração

### Acessando a Configuração

1. No painel administrativo do Magento, vá para **Lojas > Configuração**
2. Navegue até a seção **O2TI > Change Order Shipping Method**

### Opções de Configuração

| Opção | Descrição |
|-------|-----------|
| Habilitado | Ativa ou desativa o módulo |
| Status de Pedido Permitidos | Define quais status de pedido permitem a alteração do método de envio |
| Adicionar Comentário ao Pedido | Define se um comentário deve ser adicionado automaticamente ao histórico do pedido quando o método de envio for alterado |
| Notificar Cliente | Define se o cliente deve ser notificado quando o método de envio for alterado |

## Permissões

O módulo adiciona as seguintes permissões ACL:

- `O2TI_ChangeOrderShippingMethod::config` - Para acessar e modificar as configurações do módulo
- `O2TI_ChangeOrderShippingMethod::change_shipping` - Para alterar o método de envio em pedidos existentes

Para configurar as permissões:

1. Vá para **Sistema > Permissões > Funções**
2. Selecione ou crie um perfil de administrador
3. Na seção **Vendas > Operações > Pedidos**, localize a permissão "Change Order Shipping Method"
4. Na seção **Lojas > Configurações > Configuração**, localize a permissão "O2TI Change Order Shipping Method"

## Como Usar

1. Vá para **Vendas > Pedidos** no painel administrativo
2. Abra um pedido existente que esteja em um dos status permitidos
3. Acesse a aba "Alterar Método de Envio" na visualização do pedido
4. Selecione o novo método de envio na lista suspensa
5. Adicione uma descrição opcional para o método de envio
6. Clique em "Salvar" para aplicar a alteração

## Recursos Avançados

### Rastreamento de Alterações

O módulo registra automaticamente no histórico do pedido:
- O método de envio anterior
- O novo método de envio
- O nome do administrador que realizou a alteração
- Data e hora da alteração

## Suporte

Para suporte, envie um email para `brunoelisei@o2ti.com` ou visite [o2ti.com](https://www.o2ti.com) para mais informações.

## Licença

Copyright © 2025 O2TI. Todos os direitos reservados.
Veja o arquivo LICENSE para detalhes da licença.

## Autores

- Bruno Elisei (brunoelisei@o2ti.com)