# Page snapshot

```yaml
- generic [active] [ref=e1]:
  - generic [ref=e2]:
    - banner [ref=e3]:
      - generic [ref=e4]:
        - link "3D-Print Shop" [ref=e5] [cursor=pointer]:
          - /url: /
        - navigation [ref=e6]:
          - link "Каталог" [ref=e7] [cursor=pointer]:
            - /url: /shop
          - link "Кошик" [ref=e8] [cursor=pointer]:
            - /url: /cart
          - link "Кошик0,00 EUR0" [ref=e9] [cursor=pointer]:
            - /url: /cart
            - generic [ref=e10] [cursor=pointer]: Кошик
            - generic [ref=e11] [cursor=pointer]: 0,00 EUR
            - generic [ref=e12] [cursor=pointer]: "0"
          - button "Кошик0,00 EUR0" [ref=e13]:
            - generic [ref=e14]: Кошик
            - generic [ref=e15]: 0,00 EUR
            - generic [ref=e16]: "0"
    - generic [ref=e17]:
      - heading "Cart" [level=1] [ref=e18]
      - generic [ref=e19]:
        - text: Cart is empty.
        - link "Go shopping" [ref=e20] [cursor=pointer]:
          - /url: /
      - generic [ref=e21]:
        - generic [ref=e22]: "Total:"
        - generic [ref=e23]: 0,00 EUR
      - link "Checkout" [disabled] [ref=e25] [cursor=pointer]:
        - /url: /checkout
  - generic [ref=e28]:
    - generic [ref=e30]: Кошик порожній або завершений
    - button "Dismiss" [ref=e31]: ×
```