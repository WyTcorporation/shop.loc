<?php

return [
    'admin' => [
        'brand' => 'Administração da Loja',
        'navigation' => [
            'catalog' => 'Catálogo',
            'sales' => 'Vendas',
            'inventory' => 'Inventário',
            'settings' => 'Configurações',
        ],
    ],

    'navigation' => [
        'catalog' => 'Catálogo',
        'cart' => 'Carrinho',
        'order' => 'Pedido',
    ],

    'meta' => [
        'brand' => 'Shop',
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
        'statuses' => [
            'new' => 'novo',
            'paid' => 'pago',
            'shipped' => 'enviado',
            'cancelled' => 'cancelado',
        ],
        'errors' => [
            'only_new_can_be_marked_paid' => 'Apenas pedidos com o status ":required" podem ser marcados como pagos. O pedido nº:number está atualmente com o status ":status".',
            'only_paid_can_be_marked_shipped' => 'Apenas pedidos com o status ":required" podem ser marcados como enviados. O pedido nº:number está atualmente com o status ":status".',
            'only_new_or_paid_can_be_cancelled' => 'Apenas pedidos com os seguintes status podem ser cancelados: :allowed. O pedido nº:number está atualmente com o status ":status".',
        ],
    ],

    'inventory' => [
        'not_enough_stock' => 'Estoque insuficiente para o produto nº :product_id no depósito nº :warehouse_id.',
        'not_enough_reserved_stock' => 'Estoque reservado insuficiente para o produto nº :product_id no depósito nº :warehouse_id.',
    ],

    'api' => [
        'common' => [
            'not_found' => 'Recurso não encontrado.',
        ],
        'auth' => [
            'unauthenticated' => 'Não autenticado.',
            'verification_link_sent' => 'Link de verificação enviado.',
            'two_factor_required' => 'É necessário o código de autenticação em duas etapas.',
            'invalid_two_factor_code' => 'Código de autenticação em duas etapas inválido.',
        ],
        'verify_email' => [
            'invalid_signature' => 'Assinatura inválida para verificação de e-mail.',
            'already_verified' => 'E-mail já verificado.',
            'verified' => 'E-mail verificado.',
        ],
        'cart' => [
            'not_enough_stock' => 'Estoque insuficiente',
            'coupon_not_found' => 'Cupom não encontrado.',
            'coupon_not_applicable' => 'O cupom não pode ser aplicado a este carrinho.',
            'points_auth_required' => 'Apenas usuários autenticados podem resgatar pontos de fidelidade.',
        ],
        'orders' => [
            'cart_empty' => 'Carrinho vazio',
            'insufficient_stock' => 'Estoque insuficiente para o produto nº :product',
            'coupon_unavailable' => 'O cupom não está mais disponível.',
            'coupon_usage_limit_reached' => 'Limite de uso do cupom atingido.',
            'not_enough_points' => 'Pontos de fidelidade insuficientes para resgatar o valor solicitado.',
            'points_redeemed_description' => 'Pontos resgatados para o pedido :number',
            'points_earned_description' => 'Pontos acumulados no pedido :number',
        ],
        'reviews' => [
            'submitted' => 'Avaliação enviada para moderação.',
        ],
        'payments' => [
            'missing_intent' => 'payment_intent ausente.',
            'invalid_signature' => 'Assinatura do Stripe inválida.',
        ],
    ],

    'loyalty' => [
        'transaction' => [
            'earn' => 'Acumulados :points pontos de fidelidade.',
            'redeem' => 'Resgatados :points pontos de fidelidade.',
            'adjustment' => 'Saldo ajustado em :points.',
        ],
        'demo' => [
            'checkout_redeem' => 'Pontos resgatados no checkout',
            'shipped_bonus' => 'Bônus pelo pedido enviado :number',
            'cancellation_return' => 'Pontos devolvidos após cancelamento',
        ],
    ],

    'products' => [
        'fields' => [
            'name' => 'Nome',
            'slug' => 'Slug',
            'sku' => 'SKU',
            'category' => 'Categoria',
            'vendor' => 'Fornecedor',
            'preview' => 'Pré-visualização',
            'preview_url_debug' => 'URL?',
            'stock' => 'Estoque',
            'price' => 'Preço',
            'price_old' => 'Preço antigo',
            'is_active' => 'Ativo',
        ],
        'attributes' => [
            'label' => 'Atributos',
            'name' => 'Nome',
            'value' => 'Valor',
            'add' => 'Adicionar atributo',
        ],
        'placeholders' => [
            'available_stock' => 'Estoque disponível',
        ],
        'filters' => [
            'category' => 'Categoria',
            'is_active' => [
                'label' => 'Atividade',
                'true' => 'Ativos',
                'false' => 'Inativos',
            ],
        ],
    ],

    'categories' => [
        'fields' => [
            'name' => 'Nome',
            'slug' => 'Slug',
            'parent' => 'Categoria pai',
            'deleted_at' => 'Excluído em',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
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
