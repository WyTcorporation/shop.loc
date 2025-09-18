<?php

return [
    'navigation' => [
        'catalog' => 'Catálogo',
        'cart' => 'Carrinho',
        'order' => 'Pedido',
    ],

    'conversation' => [
        'heading' => 'Conversa',
        'system' => 'Sistema',
        'empty' => 'Ainda não há mensagens.',
        'new' => 'Nova mensagem',
        'send' => 'Enviar',
        'sent' => 'Mensagem enviada',
        'message' => 'Mensagem',
    ],

    'common' => [
        'owner' => 'Proprietário',
        'email' => 'E-mail',
        'phone' => 'Telefone',
        'footer_note' => 'Se tiver alguma dúvida, basta responder a este e-mail.',
        'order_title' => 'Pedido nº :number',
        'updates_email' => 'Enviaremos atualizações para :email.',
        'order_number' => 'Número do pedido',
        'items_total' => 'Total de itens',
        'coupon' => 'Cupom',
        'discount' => 'Desconto',
        'used_points' => 'Pontos utilizados',
        'order_total' => 'Total do pedido',
        'total_due' => 'Total a pagar',
        'amount_due' => 'Valor a pagar',
        'status' => 'Status',
        'shipped' => 'Enviado',
        'delivered' => 'Entregue',
        'paid' => 'Pago',
        'shipped_at' => 'Data de envio',
        'delivered_at' => 'Data de entrega',
        'paid_at' => 'Data de pagamento',
        'product' => 'Produto',
        'quantity' => 'Qtd.',
        'price' => 'Preço',
        'sum' => 'Total',
        'items_subtotal' => 'Subtotal dos itens',
    ],

    'auth' => [
        'greeting' => 'Olá, :name!',
        'reset_link_hint' => 'Um link de redefinição será enviado se a conta existir.',
        'reset' => [
            'subject' => 'Redefinição de senha para :app',
            'heading' => 'Recupere o acesso a :app',
            'intro' => 'Você está recebendo este e-mail porque recebemos uma solicitação de redefinição de senha para sua conta em :app.',
            'title' => 'Redefinir senha',
            'button' => 'Redefinir senha',
            'link_help' => 'O botão não funciona? Copie e cole este link no seu navegador:',
            'ignore' => 'Se você não solicitou a redefinição de senha, ignore este e-mail.',
            'changed_subject' => 'Senha de :app alterada',
            'changed_title' => 'Senha alterada',
            'changed_intro' => 'Acabamos de atualizar a senha da sua conta em :app.',
            'changed_warning' => 'Se você não alterou a senha, entre em contato com o suporte ou redefina-a novamente imediatamente para proteger sua conta.',
            'signature' => 'Atenciosamente, equipe :app.',
        ],
        'welcome' => [
            'subject' => 'Bem-vindo(a) ao :app!',
            'title' => 'Bem-vindo(a) ao :app',
            'intro' => 'Obrigado por se registrar no :app. Para concluir, confirme seu endereço de e-mail.',
            'button' => 'Confirmar endereço de e-mail',
            'ignore' => 'Se você não criou a conta, ignore este e-mail.',
        ],
        'verify' => [
            'subject' => 'Confirme o e-mail para :app',
            'title' => 'Confirmar endereço de e-mail',
            'intro' => 'Para ativar sua conta no :app, confirme o endereço de e-mail na próxima hora.',
            'button' => 'Confirmar endereço de e-mail',
            'ignore' => 'Se você não criou a conta, ignore este e-mail.',
        ],
    ],

    'orders' => [
        'placed' => [
            'subject' => 'Obrigado pelo pedido!',
            'subject_line' => 'Seu pedido nº :number foi recebido',
            'intro' => 'O pedido nº :number foi realizado.',
        ],
        'paid' => [
            'subject' => 'Pagamento recebido',
            'subject_line' => 'Pedido nº :number pago',
            'intro' => 'O pedido nº :number foi pago com sucesso.',
            'next' => 'Estamos preparando o envio e avisaremos sobre os próximos passos.',
            'button' => 'Voltar à loja',
        ],
        'shipped' => [
            'subject' => 'Pedido a caminho',
            'subject_line' => 'Pedido nº :number enviado',
            'intro' => 'Encaminhamos o pedido nº :number para a transportadora.',
            'next' => 'Avisaremos assim que chegar.',
            'button' => 'Acompanhar pedido',
        ],
        'delivered' => [
            'subject' => 'Pedido entregue',
            'subject_line' => 'Pedido nº :number entregue',
            'intro' => 'O pedido nº :number foi entregue com sucesso.',
            'thanks' => 'Esperamos que tenha gostado da compra. Obrigado por escolher o :app!',
            'button' => 'Ver pedido',
        ],
        'status_updated' => [
            'subject_line' => 'Status do pedido nº :number atualizado',
        ],
    ],

    'security' => [
        'two_factor' => [
            'not_initialized' => 'A autenticação em duas etapas não está configurada.',
            'invalid_code' => 'Código de autenticação em duas etapas inválido.',
            'enabled' => 'Autenticação em duas etapas ativada.',
        ],
    ],
];
